<?php 

use PhpOffice\PhpSpreadsheet\Spreadsheet;

public function readfile()
{
    $inputFileType = 'Xlsx';
    $filepath = $this->getParameter('kernel.project_dir'). '/public/test/test.xlsx'; 
    
    /**  Create a new Reader of the type defined in $inputFileType  **/
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);

    /**  Define how many rows we want to read for each "chunk"  **/
    $chunkSize = 100;
    /**  Create a new Instance of our Read Filter  **/
    $chunkFilter = new ChunkReadFilter();
    $reader->setReadEmptyCells(false);
    $reader->setReadDataOnly(true);

    /**  Tell the Reader that we want to use the Read Filter  **/
    $reader->setReadFilter($chunkFilter);

    /**  Loop to read our worksheet in "chunk size" blocks  **/
    for ($startRow = 2; $startRow <= 10000; $startRow += $chunkSize) {
        /**  Tell the Read Filter which rows we want this iteration  **/
        $chunkFilter->setRows($startRow,$chunkSize);
        /**  Load only the rows that match our filter  **/
        $spreadsheet = $reader->load($filepath);
        
        $activeRange = $spreadsheet->getActiveSheet()->calculateWorksheetDataDimension();
        $activeRange = str_replace('A1', 'A' . $startRow, $activeRange);
        $worksheet = $spreadsheet->getActiveSheet()->rangeToArray($activeRange, null, true, true, true);
        //    Do some processing here
        echo '<pre>';
        echo $startRow;
        print_r(array_filter($worksheet));
        echo '</pre>';
    }

    return (new Response('end'));
}



/**  Define a Read Filter class implementing \PhpOffice\PhpSpreadsheet\Reader\IReadFilter  */
class ChunkReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    private $startRow = 0;
    private $endRow   = 0;

    /**  Set the list of rows that we want to read  */
    public function setRows($startRow, $chunkSize) {
        $this->startRow = $startRow;
        $this->endRow   = $startRow + $chunkSize;
    }

    public function readCell($columnAddress, $row, $worksheetName = '') {
        //  Only read the heading row, and the configured rows
        if (($row >= $this->startRow && $row < $this->endRow)) {
            return true;
        }
        return false;
    }
}
