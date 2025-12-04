<?php

namespace App\Events\Inquiry;

use App\Events\AbstractEvent;
use App\Models\Inquiry;

class InquiryStatusChangedEvent extends AbstractEvent
{
    public function __construct(Inquiry $inquiry, string $oldStatus, string $newStatus, array $metadata = [])
    {
        $data = [
            'inquiry_id' => $inquiry->id,
            'inquiry_number' => $inquiry->inquiry_number,
            'user_id' => $inquiry->user_id,
            'company_name' => $inquiry->company_name,
            'contact_person' => $inquiry->contact_person,
            'email' => $inquiry->email,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'status_change_time' => now()->toISOString(),
            'changed_by' => auth()->id() ?? 'system',
            'reason' => $metadata['reason'] ?? null,
            'notes' => $metadata['notes'] ?? null,
            'priority' => $inquiry->priority,
        ];

        $defaultMetadata = [
            'source' => 'inquiry_status_change',
            'category' => 'inquiry_lifecycle',
            'importance' => 'high',
            'requires_notification' => in_array($newStatus, ['quoted', 'accepted', 'rejected']),
            'requires_follow_up' => $newStatus === 'pending'
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), true, 8);
    }

    public function getInquiryId(): int
    {
        return $this->getDataField('inquiry_id');
    }

    public function getInquiryNumber(): string
    {
        return $this->getDataField('inquiry_number');
    }

    public function getUserId(): int
    {
        return $this->getDataField('user_id');
    }

    public function getCompanyName(): string
    {
        return $this->getDataField('company_name');
    }

    public function getContactPerson(): string
    {
        return $this->getDataField('contact_person');
    }

    public function getEmail(): string
    {
        return $this->getDataField('email');
    }

    public function getOldStatus(): string
    {
        return $this->getDataField('old_status');
    }

    public function getNewStatus(): string
    {
        return $this->getDataField('new_status');
    }

    public function getStatusChangeTime(): string
    {
        return $this->getDataField('status_change_time');
    }

    public function getChangedBy(): string
    {
        return $this->getDataField('changed_by');
    }

    public function getReason(): ?string
    {
        return $this->getDataField('reason');
    }

    public function getNotes(): ?string
    {
        return $this->getDataField('notes');
    }

    public function getPriority(): string
    {
        return $this->getDataField('priority');
    }

    public function requiresNotification(): bool
    {
        return $this->getMetadataField('requires_notification', false);
    }

    public function requiresFollowUp(): bool
    {
        return $this->getMetadataField('requires_follow_up', false);
    }

    public function isStatusProgress(): bool
    {
        $statusFlow = [
            'pending' => 1,
            'processing' => 2,
            'quoted' => 3,
            'accepted' => 4,
            'rejected' => 5,
            'expired' => 6
        ];

        return ($statusFlow[$this->getNewStatus()] ?? 0) > ($statusFlow[$this->getOldStatus()] ?? 0);
    }

    public function isQuoted(): bool
    {
        return $this->getNewStatus() === 'quoted';
    }

    public function isAccepted(): bool
    {
        return $this->getNewStatus() === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->getNewStatus() === 'rejected';
    }

    public function isExpired(): bool
    {
        return $this->getNewStatus() === 'expired';
    }

    public function isHighPriority(): bool
    {
        return $this->getPriority() === 'high';
    }
}