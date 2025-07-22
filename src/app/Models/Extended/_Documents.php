<?php

namespace App\Models\Extended;

use App\Models\Document;
use App\Models\Documents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class _Documents extends Model
{
    const ENTITY_TYPE_EVENT = 1;
    
    const ENTITY_TYPE_USER = 2;
    
    const ENTITY_TYPE_PIREP = 3;

    const DOCUMENT_TYPE_IMAGE = 1;
    
    const DOCUMENT_TYPE_VIDEO = 2;
    
    const DOCUMENT_TYPE_AUDIO = 3;
    
    const DOCUMENT_TYPE_DOCUMENT = 4;

    public static function createEditDocument($entityType, $entityId, $documentData)
    {
        $document = Documents::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first();

        if (!$document) {
            // Create mode for new document
            $document = new Documents();
            // Save the document in storage/app/public/documents/document_key/document_name/version
            $documentKey = self::generateDocumentKey($entityType, $entityId, $documentData['document_name']);
            
            // Get file extension from the original filename
            $fileExtension = pathinfo($documentData['document_name'], PATHINFO_EXTENSION);
            
            // Decode base64 data and save with proper extension
            $decodedData = base64_decode($documentData['document_data']);
            $storagePath = 'documents/' . $documentKey . '/1.' . $fileExtension;
            
            Storage::disk('public')->put($storagePath, $decodedData);
            
            $document->document_name = $documentData['document_name'];
            $document->document_type = $documentData['document_type'];
            $document->document_key = $documentKey;
            $document->entity_type = $entityType;
            $document->entity_id = $entityId;
            $document->version = 1;
            $document->is_active = true;
            $document->created_at = now();
            $document->updated_at = now();
    
            if (!$document->save()) {
                return ['error' => 'Failed to create document'];
            }
        } else {
            // Edit mode for existing document
            $documentKey = $document->document_key;
            $documentVersion = $document->version;
            
            // Get file extension from the original filename
            $fileExtension = pathinfo($documentData['document_name'], PATHINFO_EXTENSION);
            
            // Remove the old document
            $oldStoragePath = 'documents/' . $documentKey . '/' . '/' . $documentVersion . '.' . $fileExtension;
            Storage::disk('public')->delete($oldStoragePath);
            
            // Save the new document with proper extension
            $decodedData = base64_decode($documentData['document_data']);
            $newStoragePath = 'documents/' . $documentKey . '/' . ($documentVersion + 1) . '.' . $fileExtension;
            
            Storage::disk('public')->put($newStoragePath, $decodedData);
            
            $document->version = $documentVersion + 1;
            $document->updated_at = now();

            if (!$document->save()) {
                return ['error' => 'Failed to update document'];
            }
        }

        return $document;
    }

    public static function fetchDocument($entityType, $entityId)
    {
        $document = Documents::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();
            
        if ($document->isEmpty()) {
            return ['error' => 'Document does not exist'];
        }

        // Get file extension from the original filename
        $fileExtension = pathinfo($document[0]->document_name, PATHINFO_EXTENSION);
        
        $documentPath = Storage::disk('public')->url('documents/' . $document[0]->document_key . '/' . $document[0]->version . '.' . $fileExtension);
        return $documentPath;
    }

    private static function generateDocumentKey($entityType, $entityId, $documentName)
    {
        return Hash::make($entityType . '_' . $entityId . '_' . Str::slug($documentName));
    }

    public static function deleteDocument($entityType, $entityId)
    {
        $document = Documents::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();

        if (!$document) {
            return ['error' => 'Document not found'];
        }

        foreach ($document as $doc) {
            $documentPath = Storage::disk('public')->url('documents/' . $doc->document_key);
            Storage::disk('public')->delete($documentPath);
            
            if (!$doc->delete()) {
                return ['error' => 'Failed to delete document'];
            }
        }

        return ['success' => 'Document deleted successfully'];
    }
}