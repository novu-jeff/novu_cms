<?php

namespace App\Services\DocumentSigning;

use App\Models\DocumentSigningRequest;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use setasign\Fpdi\Fpdi;
use Throwable;

class SignedDocumentPdfGenerator
{
    /**
     * Import the original PDF pages and append a signature
     * certification page.
     *
     * @return string Generated PDF binary content.
     */
    public function generate(DocumentSigningRequest $request): string
    {
        $disk = Storage::disk('private');
        $sourcePath = $disk->path($request->document_path);

        if (! is_file($sourcePath) || ! is_readable($sourcePath)) {
            throw new RuntimeException('The original PDF cannot be read.');
        }

        $currentHash = hash_file('sha256', $sourcePath);

        if (! $currentHash || ! hash_equals($request->document_hash, $currentHash)) {
            throw new RuntimeException('The original PDF integrity check failed.');
        }

        $request->loadMissing([
            'signers.user',
            'signers.signature',
        ]);

        $pdf = new Fpdi();

        $pdf->SetCreator(config('app.name', 'LIS'));
        $pdf->SetAuthor(config('app.name', 'LIS'));
        $pdf->SetTitle($this->pdfText($request->document_title));
        $pdf->SetSubject('Electronic Signature Certification');

        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);

        try {
            // Preserve all original pages and their dimensions.
            $pageCount = $pdf->setSourceFile($sourcePath);

            if ($pageCount < 1) {
                throw new RuntimeException('The source PDF contains no pages.');
            }

            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                $templateId = $pdf->importPage($pageNumber);
                $size = $pdf->getTemplateSize($templateId);

                $pdf->AddPage(
                    $size['orientation'],
                    [$size['width'], $size['height']]
                );

                $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height']);
            }

            // Append a separate certification page.
            $this->addCertificationPage($pdf, $request);

            return $pdf->Output('S');

        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Unable to generate the final signed PDF: '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    /**
     * Append the signing certification page.
     */
    protected function addCertificationPage(
        Fpdi $pdf,
        DocumentSigningRequest $request
    ): void {
        $pdf->AddPage('P', 'A4');
        $pdf->SetMargins(18, 18, 18);
        $pdf->SetAutoPageBreak(true, 15);

        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, $this->pdfText('ELECTRONIC SIGNATURE CERTIFICATION'), 0, 1, 'C');

        $pdf->Ln(3);

        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(
            0,
            6,
            $this->pdfText('Legislative Information System'),
            0,
            1,
            'C'
        );

        $pdf->Ln(8);

        $this->sectionHeading($pdf, 'DOCUMENT INFORMATION');

        $this->detailRow($pdf, 'Document Title', $request->document_title);
        $this->detailRow($pdf, 'Reference Number', $request->reference_no ?: 'N/A');
        $this->detailRow($pdf, 'Document Type', $request->document_type);
        $this->detailRow($pdf, 'Document ID', (string) $request->document_id);

        $pdf->Ln(4);

        $this->sectionHeading($pdf, 'ORIGINAL DOCUMENT INTEGRITY');

        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(
            0,
            6,
            $this->pdfText('Original SHA-256: '.$request->document_hash)
        );

        $pdf->Ln(6);

        $this->sectionHeading($pdf, 'SIGNING RECORD');

        foreach ($request->signers->sortBy('signing_order') as $signer) {
            if ($signer->status !== 'signed' || ! $signer->signature) {
                throw new RuntimeException(
                    'A required signer does not have a completed signature record.'
                );
            }

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(
                0,
                7,
                $this->pdfText(
                    'Signer '.$signer->signing_order.': '.$signer->signer_role
                ),
                0,
                1
            );

            $pdf->SetFont('Arial', '', 9);

            $pdf->Cell(
                0,
                6,
                $this->pdfText('Name: '.($signer->user?->name ?? 'Unknown')),
                0,
                1
            );

            $signedAt = $signer->signature->signed_at
                ?? $signer->signed_at;

            $pdf->Cell(
                0,
                6,
                $this->pdfText(
                    'Signed At: '.$signedAt?->format('F d, Y h:i:s A')
                ),
                0,
                1
            );

            $signaturePath = $signer->signature->signature_path;

            if ($signaturePath) {
                $absoluteSignaturePath = Storage::disk('private')->path($signaturePath);

                if (! is_file($absoluteSignaturePath)) {
                    throw new RuntimeException(
                        'A recorded signature image is missing.'
                    );
                }

                // Signature images are accepted as PNG by the controller.
                $imageInfo = @getimagesize($absoluteSignaturePath);

                if (! $imageInfo || $imageInfo['mime'] !== 'image/png') {
                    throw new RuntimeException(
                        'A recorded signature image is not a valid PNG.'
                    );
                }

                $pdf->Ln(2);
                $pdf->Image(
                    $absoluteSignaturePath,
                    $pdf->GetX(),
                    $pdf->GetY(),
                    45,
                    15,
                    'PNG'
                );
                $pdf->Ln(17);
            } else {
                $pdf->Cell(
                    0,
                    6,
                    $this->pdfText('Signature image: Not provided'),
                    0,
                    1
                );
            }

            $pdf->SetDrawColor(190, 190, 190);
            $pdf->Line(18, $pdf->GetY() + 2, 192, $pdf->GetY() + 2);
            $pdf->Ln(7);
        }

        $pdf->Ln(3);

        $pdf->SetFont('Arial', 'I', 8);
        $pdf->MultiCell(
            0,
            5,
            $this->pdfText(
                'This page records the electronic signing events associated with '.
                'the original document. The original document pages are preserved '.
                'without modification. This certification page is an application '.
                'record and is not a certificate-backed cryptographic PDF signature.'
            )
        );
    }

    protected function sectionHeading(Fpdi $pdf, string $heading): void
    {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(235, 238, 242);
        $pdf->Cell(0, 8, $this->pdfText($heading), 0, 1, 'L', true);
        $pdf->Ln(2);
    }

    protected function detailRow(
        Fpdi $pdf,
        string $label,
        string $value
    ): void {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(42, 7, $this->pdfText($label), 0, 0);

        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 7, $this->pdfText($value));
    }

    /**
     * Convert UTF-8 text to a character set supported by FPDF's core fonts.
     */
    protected function pdfText(?string $value): string
    {
        $value = $value ?? '';

        $converted = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $value);

        return $converted !== false ? $converted : '';
    }
}
