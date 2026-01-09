<?php
require_once('tcpdf/tcpdf.php');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$servername = "localhost";
$username = "root";
$password = "";
$database = "cicsinvsystem";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Manila');

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status_id = isset($_GET['status']) ? (int)$_GET['status'] : 0;
$dateFrom = isset($_GET['dateFrom']) ? $conn->real_escape_string($_GET['dateFrom']) : '';
$dateTo = isset($_GET['dateTo']) ? $conn->real_escape_string($_GET['dateTo']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

$sql = "SELECT e.e_ID, e.e_name, e.e_desc, e.asset_id, e.date_added,
               c.category_name, l.location_name, s.s_status
        FROM equipment_tbl e
        JOIN status_tbl s ON e.s_ID = s.s_ID
        LEFT JOIN category_tbl c ON e.category_id = c.category_id
        LEFT JOIN location_tbl l ON e.location_id = l.location_id
        WHERE 1";

if (!empty($search)) {
    $sql .= " AND (e.e_name LIKE '%$search%' OR e.e_desc LIKE '%$search%' OR e.e_ID LIKE '%$search%')";
}
if ($category_id > 0) {
    $sql .= " AND e.category_id = $category_id";
}
if ($status_id > 0) {
    $sql .= " AND e.s_ID = $status_id";
}
if (!empty($dateFrom) && !empty($dateTo)) {
    $sql .= " AND e.date_added BETWEEN '$dateFrom' AND '$dateTo'";
}

$result = $conn->query($sql);
if ($type == "pdf") {
    class MYPDF extends TCPDF {
        public function Header() {
            $printedDate = date('F j, Y \a\t h:i A') . ' PHT';
                    if ($this->getPage() == 1) {
                $this->Image('logo.jpg', 160, 15, 30);
            }
            $this->SetFont('helvetica', 'B', 14);
            $this->SetXY(15, 20);
            $this->Cell(0, 10, 'CICS TECHNICIANS EQUIPMENT REPORT - Page ' . $this->getPage(), 0, 1, 'L');
            $this->SetFont('helvetica', '', 10);
            $this->SetXY(15, 28);
            $this->Cell(0, 10, 'Printed On: ' . $printedDate, 0, 1, 'L');
            $this->SetY(40);
        }
        
        

        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->SetX(180);
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' of ' . $this->getAliasNbPages(), 0, 0, 'R');
        }
    }

    $pdf = new MYPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('CICS TECHNICIAN Equipment Report');
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(15, 45, 15);
    $pdf->SetAutoPageBreak(true, 25);
    $pdf->AddPage();

    function generateTableRows($rows) {
        $html = '';
        $rowCount = 0;
        
        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $row[$key] = empty($value) ? 'N/A' : htmlspecialchars($value);
            }

            $rowColor = ($rowCount % 2 == 0) ? '#f8d7da' : '#f1a1a6';
            $html .= "<tr style='background-color:$rowColor; color:black;'>
                        <td>{$row['e_name']}</td>
                        <td>{$row['e_desc']}</td>
                        <td>{$row['category_name']}</td>
                        <td>{$row['location_name']}</td>
                        <td>{$row['asset_id']}</td>
                        <td>{$row['s_status']}</td>
                        <td>{$row['date_added']}</td>
                      </tr>";
            $rowCount++;
        }
        
        return $html;
    }
    function getTableHeader() {
        return '<table border="1" cellpadding="5" cellspacing="0">
                <tr style="background-color:#d92332; color:white; font-weight:bold;">
                    <th width="18%">Name</th>
                    <th width="25%">Description</th>
                    <th width="13%">Category</th>
                    <th width="15%">Location</th>
                    <th width="10%">Asset ID</th>
                    <th width="9%">Status</th>
                    <th width="10%">Date Added</th>
                </tr>';
    }
    $allRows = [];
    while ($row = $result->fetch_assoc()) {
        $allRows[] = $row;
    }

    $rowsPerPage = 18;
    $chunks = array_chunk($allRows, $rowsPerPage);
        foreach ($chunks as $page => $rows) {
        if ($page > 0) {
            $pdf->AddPage();
        }
        $pdf->SetY(55);
        $pdf->SetFont('helvetica', '', 8);
        $html = getTableHeader();
        $html .= generateTableRows($rows);
        $html .= '</table>';
        $pdf->writeHTML($html, true, false, true, false, '');
    }

    $pdf->Output(date('Y-m-d') . ' CICS Technician Equipment Report.pdf', 'D');
}

elseif ($type == "excel") {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('CICS Equipment Report');

    // Title
    $sheet->setCellValue('A1', 'CICS TECHNICIAN EQUIPMENT REPORT');
    $sheet->mergeCells('A1:H1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

    // Timestamp
    $sheet->setCellValue('A2', 'Generated on: ' . date('Y-m-d H:i:s'));
    $sheet->mergeCells('A2:H2');
    $sheet->getStyle('A2')->getFont()->setSize(12);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $headers = ['Equipment ID', 'Name', 'Description', 'Category', 'Location', 'Asset ID', 'Status', 'Date Added'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '3', $header);
        $col++;
    }

    $headerStyle = [
        'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'D92332']
        ],
        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
        'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
    ];
    $sheet->getStyle('A3:H3')->applyFromArray($headerStyle);
    $rowNumber = 4;
    $alternate = true;

    while ($row = $result->fetch_assoc()) {
        foreach ($row as $key => $value) {
            if (empty($value)) $row[$key] = 'N/A';
        }

        $sheet->setCellValue("A$rowNumber", $row['e_ID']);
        $sheet->setCellValue("B$rowNumber", $row['e_name']);
        $sheet->setCellValue("C$rowNumber", $row['e_desc']);
        $sheet->setCellValue("D$rowNumber", $row['category_name']);
        $sheet->setCellValue("E$rowNumber", $row['location_name']);
        $sheet->setCellValue("F$rowNumber", $row['asset_id']);
        $sheet->setCellValue("G$rowNumber", $row['s_status']);
        $sheet->setCellValue("H$rowNumber", date('M d, Y', strtotime($row['date_added'])));
        $fillColor = $alternate ? 'FFFFFF' : 'F5F5F5'; // White and light gray nalang
        $rowStyle = [
            'font' => ['size' => 12 ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => $fillColor]
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ];
        $sheet->getStyle("A$rowNumber:H$rowNumber")->applyFromArray($rowStyle);
        $sheet->getStyle("A$rowNumber:H$rowNumber")->getAlignment()->setWrapText(true);

        $alternate = !$alternate;
        $rowNumber++;
    }

    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    $pageSetup = $sheet->getPageSetup();
    $pageSetup->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
    $pageSetup->setFitToWidth(1);
    $pageSetup->setFitToHeight(0);
    $pageSetup->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    $writer = new Xlsx($spreadsheet);
    $filename = date('Y-m-d') . ' CICS Technician Equipment Report.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
}

 
?>
