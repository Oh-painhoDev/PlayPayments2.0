<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'front_document',
        'back_document',
        'selfie_document',
        'proof_address',
        'income_proof',
        'financial_statement',
        'rejection_reason',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the document verification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed the documents.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if documents are pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Check if documents are approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'aprovado';
    }

    /**
     * Check if documents are rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'recusado';
    }

    /**
     * Check if all required documents are uploaded
     */
    public function hasAllDocuments(): bool
    {
        $user = $this->user;
        
        if (!$user) {
            return false;
        }
        
        // Documentos obrigatórios para todos
        $required = [
            'front_document',
            'back_document', 
            'selfie_document',
        ];

        // Para pessoa jurídica, adicionar Contrato Social (proof_address)
        if ($user->isPessoaJuridica()) {
            $required[] = 'proof_address';
        }

        foreach ($required as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing documents
     */
    public function getMissingDocuments(): array
    {
        $user = $this->user;
        
        if (!$user) {
            return ['Erro: usuário não encontrado'];
        }
        
        $missing = [];
        
        $documents = [
            'front_document' => $user->isPessoaFisica() ? 'Frente do documento' : 'Frente do RG/CNH',
            'back_document' => $user->isPessoaFisica() ? 'Verso do documento' : 'Verso do RG/CNH',
            'selfie_document' => 'Selfie com documento',
        ];

        // Para pessoa jurídica, adicionar Contrato Social
        if ($user->isPessoaJuridica()) {
            $documents['proof_address'] = 'Contrato Social';
        }

        foreach ($documents as $field => $name) {
            if (empty($this->$field)) {
                $missing[] = $name;
            }
        }

        return $missing;
    }

    /**
     * Get formatted submitted date
     */
    public function getFormattedSubmittedAtAttribute(): ?string
    {
        if (!$this->submitted_at) {
            return null;
        }

        return $this->submitted_at->format('d/m/Y H:i');
    }

    /**
     * Get formatted reviewed date
     */
    public function getFormattedReviewedAtAttribute(): ?string
    {
        if (!$this->reviewed_at) {
            return null;
        }

        return $this->reviewed_at->format('d/m/Y H:i');
    }
}
