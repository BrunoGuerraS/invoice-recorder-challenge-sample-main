<?php

namespace App\Services;

use App\Events\Vouchers\VouchersCreated;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherLine;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use SimpleXMLElement;

class VoucherService
{
    public function getVouchers(int $page, int $paginate): LengthAwarePaginator
    {
        return Voucher::with(['lines', 'user'])->paginate(perPage: $paginate, page: $page);

    }

    /**
     * @param string[] $xmlContents
     * @param User $user
     * @return Voucher[]
     */
    public function storeVouchersFromXmlContents(array $xmlContents, User $user): array
    {
        $vouchersProcessed = [];
        $vouchersFailed = [];

        $vouchers = [];
        foreach ($xmlContents as $xmlContent) {
            try {
                $vouchers[]  = $this->storeVoucherFromXmlContent($xmlContent, $user);
                $vouchersProcessed = $vouchers;
            } catch (Exception $e) {
                $vouchersFailed[] = ['xml_content' => $xmlContent, 'error' => $e->getMessage()];
            }
        }

        VouchersCreated::dispatch($vouchers, $user);

        return $vouchers;
    }

    public function storeVoucherFromXmlContent(string $xmlContent, User $user): Voucher
    {
        $xml = new SimpleXMLElement($xmlContent);

        $issuerName = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyName/cbc:Name')[0];
        $issuerDocumentType = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $issuerDocumentNumber = (string) $xml->xpath('//cac:AccountingSupplierParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];

        $receiverName = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyLegalEntity/cbc:RegistrationName')[0];
        $receiverDocumentType = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID/@schemeID')[0];
        $receiverDocumentNumber = (string) $xml->xpath('//cac:AccountingCustomerParty/cac:Party/cac:PartyIdentification/cbc:ID')[0];
        $totalAmount = (string) $xml->xpath('//cac:LegalMonetaryTotal/cbc:TaxInclusiveAmount')[0];

        $serie = (string) explode('-', (string) $xml->xpath('//cbc:ID')[0])[0];
        $voucherNumber = (string) explode('-', (string) $xml->xpath('//cbc:ID')[0])[1];
        $voucherType = (string) $xml->xpath('//cbc:InvoiceTypeCode')[0];
        $currencyType = (string) $xml->xpath('//cbc:DocumentCurrencyCode')[0];

        $voucher = new Voucher([
            'issuer_name' => $issuerName,
            'issuer_document_type' => $issuerDocumentType,
            'issuer_document_number' => $issuerDocumentNumber,
            'receiver_name' => $receiverName,
            'receiver_document_type' => $receiverDocumentType,
            'receiver_document_number' => $receiverDocumentNumber,
            'total_amount' => $totalAmount,
            'xml_content' => $xmlContent,
            'user_id' => $user->id,
            'serie' => $serie,
            'voucher_number' => $voucherNumber,
            'voucher_type' => $voucherType,
            'currency_type' => $currencyType
        ]);
        $voucher->save();

        foreach ($xml->xpath('//cac:InvoiceLine') as $invoiceLine) {
            $name = (string) $invoiceLine->xpath('cac:Item/cbc:Description')[0];
            $quantity = (float) $invoiceLine->xpath('cbc:InvoicedQuantity')[0];
            $unitPrice = (float) $invoiceLine->xpath('cac:Price/cbc:PriceAmount')[0];

            $voucherLine = new VoucherLine([
                'name' => $name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'voucher_id' => $voucher->id,
            ]);

            $voucherLine->save();
        }

        $voucherWithoutXmlContent = clone $voucher;
        unset($voucherWithoutXmlContent->xml_content);

        return $voucherWithoutXmlContent;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function getTotalAmount(string $id)
    {
        $result = Voucher::selectRaw('user_id')
            ->selectRaw('SUM(CASE WHEN currency_type = "PEN" THEN total_amount ELSE 0 END) AS total_amount_sum_pen')
            ->selectRaw('SUM(CASE WHEN currency_type = "USD" THEN total_amount ELSE 0 END) AS total_amount_sum_usd')
            ->where('user_id', $id)
            ->groupBy('user_id')
            ->get();

        return $result;
    }

    /**
     * @param string $id
     * @return string
     */
    public function deleteVoucher(string $id)
    {
        try {
            $voucher = Voucher::findOrFail($id);
            $voucher->delete();
            return 'Voucher deleted successfully.';
        } catch (Exception $exception) {
            return 'Voucher not found.';
        }
    }
}
