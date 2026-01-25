<?php

namespace App\Traits;

use DB;
trait Generate_MIGTraits
{
    public function generate_MIG($data)
    {
        DB::connection('mysql');
        $value['orderNum'] = $data['orderNum'];
        $orderDetail = DB::select("SELECT * FROM order_detail WHERE unitPrice>0 and orderNum=?", [$value['orderNum']]);
        $value['invoiceNumber'] = $data['invoiceNumber'];
        $value['currentDate'] = date("Ymd");
        $value['currentTime'] = date("h:i:s");
        $value['sellTax'] = $data['sellTax'];
        $value['sellName'] = $data['sellName'];
        $value['buyTax'] = $data['buyTax'];
        $value['buyName'] = $data['buyName'];
        $value['invoiceType'] = $data['invoiceType'];
        $value['donate'] = $data['donate'];
        $value['orderDetail'] = $orderDetail;
        $value['directoryName'] = $data['orderNum'];
        return $value;
    }
    //B2C
    public function write_C0401XML($value)
    {
        $filed = '<?xml version="1.0" encoding="utf-8"?>
    <Invoice xsi:schemaLocation="urn:GEINV:eInvoiceMessage:C0401:3.2 C0401.xsd" xmlns="urn:GEINV:eInvoiceMessage:C0401:3.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
        <Main>
            <InvoiceNumber>' . $value['invoiceNumber']; $filed = $filed . '</InvoiceNumber>
            <InvoiceDate>' . $value['currentDate']; $filed = $filed . '</InvoiceDate>
            <InvoiceTime>' . $value['currentTime']; $filed = $filed . '</InvoiceTime>
            <Seller>
                <Identifier>' . $value['sellTax']; $filed = $filed . '</Identifier>
                <Name>' . $value['sellName']; $filed = $filed . '</Name>
            </Seller>
            <Buyer>
                <Identifier>' . $value['buyTax']; $filed = $filed . '</Identifier>  
                <Name>' . $value['buyName']; $filed = $filed . '</Name> 
            </Buyer>
            <InvoiceType>' . $value['invoiceType']; $filed = $filed . '</InvoiceType>
            <DonateMark>' . $value['donate']; $filed = $filed . '</DonateMark>
            <PrintMark>' . $value['printMark']; $filed = $filed . '</PrintMark>
            <RandomNumber>' . $value['randomNum']; $filed = $filed . '</RandomNumber>
        </Main>
        <Details>';
            for ($i = 0; $i < count($value['orderDetail']); $i++) {
                $filed = $filed . '
            <ProductItem>
                <Description>' . $value['orderDetail'][$i]->productName; $filed = $filed . '</Description>
                <Quantity>' .intval(ceil( $value['orderDetail'][$i]->quantity)); $filed = $filed . '</Quantity>
                <UnitPrice>' .intval(ceil( $value['orderDetail'][$i]->unitPrice)); $filed = $filed . '</UnitPrice>
                <Amount>' . intval(ceil($value['orderDetail'][$i]->finalPrice)); $filed = $filed . '</Amount>
                <SequenceNumber>' . $i; $filed = $filed . '</SequenceNumber>
            </ProductItem>';
            }
            $filed = $filed . '
        </Details>
        <Amount>
            <SalesAmount>' . intval(ceil($value['salesAmount'])); $filed = $filed . '</SalesAmount>
            <FreeTaxSalesAmount>' . intval(ceil( $value['freeTaxSalesAmount'])); $filed = $filed . '</FreeTaxSalesAmount>
            <ZeroTaxSalesAmount>' . intval(ceil( $value['zeroTaxSalesAmount'])); $filed = $filed . '</ZeroTaxSalesAmount>
            <TaxType>' . $value['taxType']; $filed = $filed . '</TaxType>
            <TaxRate>' . $value['taxRate']; $filed = $filed . '</TaxRate>
            <TaxAmount>' .  intval(ceil($value['taxAmount'])); $filed = $filed . '</TaxAmount>
            <TotalAmount>' .  intval(ceil($value['totalAmount'])); $filed = $filed . '</TotalAmount>
        </Amount>
    </Invoice>';

        $xml = new \SimpleXMLElement($filed);
        $current = date("Ymd");

        $file_path = '../../dalimarket/image/mig/' . $current;
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/mig/' . $current . '/C0401';
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/invoice/' . $value['directoryName'];
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }

        $file = '../../dalimarket/image/invoice/' . $value['directoryName'] . '/' . $value['directoryName'] . '.xml';
        $xml->asXml($file);

        $files = '../../dalimarket/image/mig/' . $current . '/C0401/' . $value['invoiceNumber'] . '.xml';
        $xml->asXml($files);
        return $filed;
    }
        //B2B
    public function write_A0401XML($value)
    {
        $filed = '<?xml version="1.0" encoding="utf-8"?>
        <Invoice xsi:schemaLocation="urn:GEINV:eInvoiceMessage:A0401:3.2 A0401.xsd" xmlns="urn:GEINV:eInvoiceMessage:A0401:3.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
            <Main>
                <InvoiceNumber>' . $value['invoiceNumber']; $filed = $filed . '</InvoiceNumber>
                <InvoiceDate>' . $value['currentDate']; $filed = $filed . '</InvoiceDate>
                <InvoiceTime>' . $value['currentTime']; $filed = $filed . '</InvoiceTime>
                <Seller>
                    <Identifier>' . $value['sellTax']; $filed = $filed . '</Identifier>
                    <Name>' . $value['sellName']; $filed = $filed . '</Name>
                </Seller>
                <Buyer>
                    <Identifier>' . $value['buyTax']; $filed = $filed . '</Identifier>
                    <Name>' . $value['buyName']; $filed = $filed . '</Name>
                </Buyer>
                <InvoiceType>' . $value['invoiceType']; $filed = $filed . '</InvoiceType>
                <DonateMark>' . $value['donate']; $filed = $filed . '</DonateMark>
            </Main>
            <Details>';
            for ($i = 0; $i < count($value['orderDetail']); $i++) {
                $filed = $filed . '
                <ProductItem>
                    <Description>' . $value['orderDetail'][$i]->productName; $filed = $filed . '</Description>
                    <Quantity>' . intval(ceil($value['orderDetail'][$i]->quantity)); $filed = $filed . '</Quantity>
                    <UnitPrice>' . intval(ceil($value['orderDetail'][$i]->unitPrice)); $filed = $filed . '</UnitPrice>
                    <Amount>' . intval(ceil($value['orderDetail'][$i]->finalPrice)); $filed = $filed . '</Amount>
                    <SequenceNumber>' . $i; $filed = $filed . '</SequenceNumber>
                </ProductItem>';
            }
            $filed = $filed . '
            </Details>
            <Amount>
                <SalesAmount>' . intval(ceil($value['salesAmount'])); $filed = $filed . '</SalesAmount>
                <TaxType>' . $value['taxType']; $filed = $filed . '</TaxType>
                <TaxRate>' . $value['taxRate']; $filed = $filed . '</TaxRate>
                <TaxAmount>' . $value['taxAmount']; $filed = $filed . '</TaxAmount>
                <TotalAmount>' . $value['totalAmount']; $filed = $filed . '</TotalAmount>
            </Amount>
        </Invoice>';

        $xml = new \SimpleXMLElement($filed);
        $current = strval(date("Ymd"));
        $file_path = '../../dalimarket/image/mig/' . $current;
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/mig/' . $current . '/A0401';
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/invoice/' . $value['directoryName'];
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/invoice/' . $value['directoryName'];
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }

        $file = '../../dalimarket/image/invoice/' . $value['directoryName'] . '/' . $value['directoryName'] . '.xml';
        $xml->asXml($file);

        $files = '../../dalimarket/image/mig/' . $current . '/A0401/' . $value['invoiceNumber'] . '.xml';
        $xml->asXml($files);
        return $filed;
    }
}
