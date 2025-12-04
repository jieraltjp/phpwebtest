<?php

namespace App\Events\Inquiry;

use App\Events\AbstractEvent;
use App\Models\Inquiry;

class InquiryCreatedEvent extends AbstractEvent
{
    public function __construct(Inquiry $inquiry, array $metadata = [])
    {
        $data = [
            'inquiry_id' => $inquiry->id,
            'inquiry_number' => $inquiry->inquiry_number,
            'user_id' => $inquiry->user_id,
            'company_name' => $inquiry->company_name,
            'contact_person' => $inquiry->contact_person,
            'email' => $inquiry->email,
            'phone' => $inquiry->phone,
            'subject' => $inquiry->subject,
            'message' => $inquiry->message,
            'status' => $inquiry->status,
            'priority' => $inquiry->priority,
            'estimated_budget' => $inquiry->estimated_budget,
            'currency' => $inquiry->currency,
            'quantity' => $inquiry->quantity,
            'created_at' => $inquiry->created_at->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        $defaultMetadata = [
            'source' => 'inquiry_creation',
            'category' => 'inquiry_lifecycle',
            'importance' => 'high',
            'requires_notification' => true,
            'requires_follow_up' => true
        ];

        parent::__construct($data, array_merge($defaultMetadata, $metadata), true, 10);
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

    public function getPhone(): ?string
    {
        return $this->getDataField('phone');
    }

    public function getSubject(): string
    {
        return $this->getDataField('subject');
    }

    public function getMessage(): string
    {
        return $this->getDataField('message');
    }

    public function getStatus(): string
    {
        return $this->getDataField('status');
    }

    public function getPriority(): string
    {
        return $this->getDataField('priority');
    }

    public function getEstimatedBudget(): ?float
    {
        return $this->getDataField('estimated_budget');
    }

    public function getCurrency(): string
    {
        return $this->getDataField('currency');
    }

    public function getQuantity(): ?int
    {
        return $this->getDataField('quantity');
    }

    public function getCreatedAt(): string
    {
        return $this->getDataField('created_at');
    }

    public function getIpAddress(): string
    {
        return $this->getDataField('ip_address');
    }

    public function getUserAgent(): string
    {
        return $this->getDataField('user_agent');
    }

    public function requiresNotification(): bool
    {
        return $this->getMetadataField('requires_notification', false);
    }

    public function requiresFollowUp(): bool
    {
        return $this->getMetadataField('requires_follow_up', false);
    }

    public function isHighPriority(): bool
    {
        return $this->getPriority() === 'high';
    }

    public function hasBudget(): bool
    {
        return $this->getEstimatedBudget() !== null;
    }

    public function hasQuantity(): bool
    {
        return $this->getQuantity() !== null;
    }
}