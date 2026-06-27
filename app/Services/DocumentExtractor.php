<?php

namespace App\Services;

use App\Models\Document;
use DOMDocument;
use Exception;
use ZipArchive;
use Illuminate\Support\Facades\Log;

class DocumentExtractor
{
    /**
     * Extrai texto de um arquivo .docx.
     *
     * @param string $filePath Caminho completo do arquivo
     * @return string Texto extraído
     * @throws Exception Se não conseguir ler o arquivo
     */
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo não encontrado: $filePath");
        }
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if ($extension === 'docx') {
            return $this->extractFromDocx($filePath);
        } elseif ($extension === 'doc') {
            return "[Arquivo .doc antigo - Por favor, converta para .docx para extração de conteúdo]";
        } else {
            throw new Exception("Formato não suportado: $extension");
        }
    }
    
    /**
     * Extrai texto de arquivo .docx usando ZipArchive.
     *
     * @param string $filePath Caminho do arquivo .docx
     * @return string Texto extraído
     */
    private function extractFromDocx(string $filePath): string
    {
        $zip = new ZipArchive();
        
        if ($zip->open($filePath) !== true) {
            throw new Exception("Não foi possível abrir o arquivo como ZIP");
        }
        
        $content = $zip->getFromName('word/document.xml');
        $zip->close();
        
        if ($content === false) {
            throw new Exception("Não foi possível encontrar document.xml no arquivo");
        }
        
        return $this->extractTextFromXml($content);
    }
    
    /**
     * Extrai texto puro do XML do Word.
     *
     * @param string $xml Conteúdo XML
     * @return string Texto extraído
     */
    private function extractTextFromXml(string $xml): string
    {
        $xml = str_replace(['w:', 'w:'], '', $xml);
        
        $dom = new DOMDocument();
        @$dom->loadXML($xml);
        
        $textNodes = $dom->getElementsByTagName('t');
        
        $text = '';
        foreach ($textNodes as $node) {
            $text .= $node->nodeValue . ' ';
        }
        
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
    
    /**
     * Extrai e salva conteúdo de um documento.
     *
     * @param int $documentId ID do documento no banco
     * @return array Resultado com sucesso e mensagem
     */
    public function extractAndSave(int $documentId): array
    {
        try {
            $document = Document::findOrFail($documentId);
            $filePath = $document->file_path;
            
            // Determinar o caminho completo com base em prováveis localizações
            if (file_exists($filePath)) {
                $fullPath = $filePath;
            } elseif (file_exists(public_path($filePath))) {
                $fullPath = public_path($filePath);
            } elseif (file_exists(public_path('uploads/' . $filePath))) {
                $fullPath = public_path('uploads/' . $filePath);
            } elseif (file_exists(storage_path('app/' . $filePath))) {
                $fullPath = storage_path('app/' . $filePath);
            } elseif (file_exists(storage_path('app/public/' . $filePath))) {
                $fullPath = storage_path('app/public/' . $filePath);
            } else {
                $fullPath = base_path($filePath);
                if (!file_exists($fullPath)) {
                    Log::error("DocumentExtractor: Arquivo não encontrado: $filePath");
                    return [
                        'success' => false,
                        'message' => "Arquivo não encontrado no servidor."
                    ];
                }
            }
            
            $text = $this->extractText($fullPath);
            
            $document->update([
                'content_text' => $text,
                'content_extracted_at' => now(),
            ]);
            
            return [
                'success' => true,
                'message' => 'Conteúdo extraído com sucesso',
                'text_length' => strlen($text),
                'preview' => mb_substr($text, 0, 200) . '...'
            ];
            
        } catch (Exception $e) {
            Log::error("DocumentExtractor::extractAndSave error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
}
