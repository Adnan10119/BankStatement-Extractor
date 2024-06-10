<?php

namespace App\Exports;

use App\Models\MakeXLSX;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
// use Maatwebsite\Excel\Concerns\WithStyles;
// use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
// use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
// use PhpOffice\PhpSpreadsheet\Style\Color;
// use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
// use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
// use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Auth;
// use PhpOffice\PhpSpreadsheet\Style\Conditional;
//extends StringValueBinders
class XLSXExport implements FromCollection, WithHeadings,
 ShouldAutoSize, WithEvents//, WithCustomValueBinder//, WithColumnFormatting
//  , WithMapping
{
    private $user_id;
    use RegistersEventListeners;
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($id)
    {
        $this->user_id = $id;
    }

    public function collection()
    {
        return MakeXLSX::where('user_id', $this->user_id)->get(['index','account_name','account_number','account_type','ck','date'
        ,'description','debit','credit','value','balance','statement_balance','difference']);
    }
    public function headings(): array
    {
        return [
            'Index', 'Account Name', 'Account Number', 'Account Type', 'Ck#', 'Date',
            'Description', 'Debit', 'Credit', 'Value', 'Balance', 'Statement Balance',
            'Difference'
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {

        $styleArray = array(
            'font'  => array(
                'color' => array('rgb' => 'FF0000'),
            ),
            ''
        );

        $sheet = $event->sheet->getDelegate();
        $highestRow = $event->sheet->getHighestRow();
        $highestColumn = $event->sheet->getHighestColumn();
        // $checkStatementBalance = 0;
        // $check1 = $event->sheet->getCellByColumnAndRow(12, 3)->getValue();
        // $check2 = $event->sheet->getCellByColumnAndRow(12, 4)->getValue();
        // $check3 = $event->sheet->getCellByColumnAndRow(12, 5)->getValue();
        // if(empty($check1) && empty($check2) && empty($check3)){
            // $checkStatementBalance = 1;
        // }
        // \Log::debug("Check statement check is : ".$checkStatementBalance);
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        for ($row = 2; $row <= $highestRow; ++$row) {
            try{
                if($row != 2){
                    // $x = floatval($event->sheet->getCellByColumnAndRow(11, $row-1)->getValue());
                    // $y = floatval($event->sheet->getCellByColumnAndRow(10, $row)->getValue());
                    // $value = $x+$y;
                    // $value = number_format($value, 2);
                    // $value = str_replace(',','',$value);
                    // $value = '=L'. (int)$row-1 . '+K' . (int)$row;
                    // \Log::Debug("index k : ". ($row-1));
                    // \Log::Debug("index j : ".$row);
                    $formulaeBalance = "=ROUND(IF(ISNUMBER(K".($row-1)."), K".($row-1)." + I".$row." - H".$row.", I".$row." - H".$row."), 5)";
                    // $event->sheet->setCellValue('K'. $row, '=K'.($row-1).'+J'.($row));
                    $event->sheet->setCellValue('K'. $row,$formulaeBalance);
                }

                $valFormulae = "=ROUND(I".$row."-H".$row.", 5)";
                // $event->sheet->setCellValue('J'. $row, '=I'.($row).'-H'.($row));
                $event->sheet->setCellValue('J'. $row, $valFormulae);
                // $x1 = $event->sheet->getCellByColumnAndRow(11, $row)->getValue();
                // $y1 = $event->sheet->getCellByColumnAndRow(12, $row)->getValue();
                // \Log::info($y1);
            }
            catch(\Exception $e){
                $event->sheet->setCellValue('K'. $row, "Error while calculation!");
            }
            try{
                // =IF(ROUND(K10-L10,1)=0,"-",K10-L10)
                $formulae = '=IF(ISBLANK(L'.$row.'),"",IF(ROUND(K'.$row.'-L'.$row.',1)=0,"-",K'.$row.'-L'.$row.'))';
                $event->sheet->setCellValue('M'. $row, $formulae);
                // if($x1-$y1 == 0){
                //     $event->sheet->setCellValue('M'. $row, "-");
                // }
                // else{
                //     $value = $x1+$y1;
                //     $value = number_format($value, 2);
                //     $value = str_replace(',','',$value);
                //     if($y1 == ''){
                //         $event->sheet->setCellValue('M'. $row, "-");
                //     }
                //     else{
                //         $event->sheet->setCellValue('M'. $row, $value);
                //     }
                // }
            }
            catch(\Exception $e){
                $event->sheet->setCellValue('M'. $row, "Error while calculating difference!");
            }
            $slaHours = $event->sheet->getCellByColumnAndRow(10, $row)->getValue();
            if((int)$slaHours < 0){
                $event->sheet->getCellByColumnAndRow(10, $row)->getStyle()->applyFromArray($styleArray);
            }
        }
        $sheet->getStyle('C')->getNumberFormat()->setFormatCode("0");
        $sheet->getStyle('H')->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('I')->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('K')->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('L')->getNumberFormat()->setFormatCode("#,##0.00");
        $sheet->getStyle('M')->getNumberFormat()->setFormatCode("#,##0.00");
        // $sheet->getStyle('M')->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        $sheet->getStyle('M')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('K')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('L')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    }

    // public function styles(Worksheet $sheet)
    // {
    //     return [
    //         // Style the first row as bold text.
    //         "1"    => ['align' => ['right' => true]],

    //         // Styling a specific cell by coordinate.
    //         'B2' => ['font' => ['italic' => true]],

    //         // Styling an entire column.
    //         'C'  => ['font' => ['size' => 16]],
    //     ];
    // }

    // public function bindValue(Cell $cell, $value)
    // {
    //     if (is_numeric($value)) {
    //         $cell->setValueExplicit($value, DataType::TYPE_STRING);

    //         return true;
    //     }

    //     // else return default behavior
    //     return parent::bindValue($cell, $value);
    // }

    // public function columnFormats(): array
    // {
    //     return [
    //         // 'H' => NumberFormat::FORMAT_TEXT,
    //         // 'I' => NumberFormat::FORMAT_TEXT,
    //         // 'J' => NumberFormat::FORMAT_TEXT,
    //         // 'K' => NumberFormat::FORMAT_TEXT,
    //         // 'L' => NumberFormat::FORMAT_TEXT,
    //         // 'M' => NumberFormat::FORMAT_TEXT,
    //             // 'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
    //             // 'B' => NumberFormat::FORMAT_NUMBER,
    //             // 'C' => NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE,
    //             // 'D' => NumberFormat::FORMAT_TEXT,
    //     ];
    // }

    // public function map($invoice): array
    // {
    //     \Log::debug("Geting invoice");
    //     \Log::debug($invoice);
    //     \Log::debug("closing invoice");
    //     $row = (int)$invoice->index + 1;
    //     return [
    //         $invoice->index,
    //         $invoice->account_name,
    //         $invoice->account_number,
    //         $invoice->account_type,
    //         $invoice->ck,
    //         $invoice->date,
    //         $invoice->description,
    //         $invoice->debit,
    //         $invoice->credit,
    //         $invoice->value,
    //         $invoice->balance,
    //         $invoice->statement_balance,
    //         '=IF(K'.$row.'-L'.$row.'=0,"-",K'.$row.'-L'.$row.')'
    //     ];
    // }

    // public function registerEvents(): array
    // {

    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {


                /* This makes the header row bold. */
                // $event->sheet->getStyle('A1:M1')->applyFromArray([
                //     'font' => [
                //         'bold' => true,
                //     ],
                // ]);
                // $objConditionalStyle = new PHPExcel_Style_Conditional();
                // $objConditionalStyle->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS)
                //     ->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_LESSTHAN)
                //     ->addCondition('12');
                // $objConditionalStyle->getStyle()->getFont()->getColor()->setRGB('FF0000');
                // $objConditionalStyle->getStyle()->getFont()->setBold(true);

                // $conditionalStyles = $event->getStyle('A3')
                //     ->getConditionalStyles();
                // array_push($conditionalStyles, $objConditionalStyle);
                // $event->getActiveSheet()->getStyle('A3')
                //     ->setConditionalStyles($conditionalStyles);

    //             $conditional1 = new Conditional();
    //             $conditional1->setConditionType(Conditional::CONDITION_CONTAINSTEXT);
    //             $conditional1->setOperatorType(Conditional::OPERATOR_LESSTHAN);
    //             $conditional1->addCondition('50');
    //             $conditional1->getStyle()->getFill()
    //             ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //             ->getStartColor()
    //             ->setARGB('DD4B39');
    //             $conditional1->getStyle()->getFill()
    //             ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //             ->getStartColor()
    //             ->setARGB('DD4B39');
    //             $conditionalStyles[] = $conditional1;

    //             $event->getSheet()->getStyle('J')->setConditionalStyles($conditionalStyles);
    //         },
    //     ];
    // }
}
