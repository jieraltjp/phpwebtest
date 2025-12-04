<?php

declare(strict_types=1);

namespace App\Domain\Inquiry;

use App\Domain\Inquiry\ValueObjects\InquiryId;
use App\Domain\Inquiry\ValueObjects\InquiryStatus;
use App\Domain\Abstractions\AbstractAggregateRoot;

final class Inquiry extends AbstractAggregateRoot
{
    private InquiryId $id;
    private string $customerId;
    private string $customerEmail;
    private string $customerPhone;
    private string $companyName;
    private string $subject;
    private string $message;
    private array $productIds;
    private ?float $quotedPrice;
    private ?string $quotedCurrency;
    private ?string $notes;
    private InquiryStatus $status;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $quotedAt;
    private ?\DateTimeImmutable $expiresAt;
    private ?string $handledBy;

    private function __construct(
        InquiryId $id,
        string $customerId,
        string $customerEmail,
        string $customerPhone,
        string $companyName,
        string $subject,
        string $message,
        array $productIds = []
    ) {
        parent::__construct($id->toString());
        $this->id = $id;
        $this->customerId = $customerId;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
        $this->companyName = $companyName;
        $this->subject = $subject;
        $this->message = $message;
        $this->productIds = $productIds;
        $this->quotedPrice = null;
        $this->quotedCurrency = null;
        $this->notes = null;
        $this->status = InquiryStatus::pending();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = null;
        $this->quotedAt = null;
        $this->expiresAt = null;
        $this->handledBy = null;
    }

    public static function create(
        InquiryId $id,
        string $customerId,
        string $customerEmail,
        string $customerPhone,
        string $companyName,
        string $subject,
        string $message,
        array $productIds = []
    ): self {
        return new self($id, $customerId, $customerEmail, $customerPhone, $companyName, $subject, $message, $productIds);
    }

    public static function createExisting(
        InquiryId $id,
        string $customerId,
        string $customerEmail,
        string $customerPhone,
        string $companyName,
        string $subject,
        string $message,
        array $productIds,
        ?float $quotedPrice,
        ?string $quotedCurrency,
        ?string $notes,
        InquiryStatus $status,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $updatedAt = null,
        ?\DateTimeImmutable $quotedAt = null,
        ?\DateTimeImmutable $expiresAt = null,
        ?string $handledBy = null
    ): self {
        $inquiry = new self($id, $customerId, $customerEmail, $customerPhone, $companyName, $subject, $message, $productIds);
        $inquiry->quotedPrice = $quotedPrice;
        $inquiry->quotedCurrency = $quotedCurrency;
        $inquiry->notes = $notes;
        $inquiry->status = $status;
        $inquiry->createdAt = $createdAt;
        $inquiry->updatedAt = $updatedAt;
        $inquiry->quotedAt = $quotedAt;
        $inquiry->expiresAt = $expiresAt;
        $inquiry->handledBy = $handledBy;

        return $inquiry;
    }

    public function getId(): InquiryId
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getProductIds(): array
    {
        return $this->productIds;
    }

    public function getQuotedPrice(): ?float
    {
        return $this->quotedPrice;
    }

    public function getQuotedCurrency(): ?string
    {
        return $this->quotedCurrency;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getStatus(): InquiryStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getQuotedAt(): ?\DateTimeImmutable
    {
        return $this->quotedAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getHandledBy(): ?string
    {
        return $this->handledBy;
    }

    public function addQuote(float $price, string $currency = 'CNY', ?string $notes = null, ?string $handledBy = null): void
    {
        if (!$this->status->canBeQuoted()) {
            throw new \InvalidArgumentException('Inquiry cannot be quoted in current status');
        }

        $this->quotedPrice = $price;
        $this->quotedCurrency = $currency;
        $this->notes = $notes;
        $this->handledBy = $handledBy;
        $this->quotedAt = new \DateTimeImmutable();
        $this->expiresAt = new \DateTimeImmutable('+30 days'); // Quote expires in 30 days
        
        $this->changeStatus(InquiryStatus::quoted(), 'Quote provided', $handledBy);
    }

    public function accept(?string $acceptedBy = null): void
    {
        if (!$this->status->canBeAccepted()) {
            throw new \InvalidArgumentException('Inquiry cannot be accepted in current status');
        }

        $this->changeStatus(InquiryStatus::accepted(), 'Quote accepted by customer', $acceptedBy);
    }

    public function reject(?string $reason = null, ?string $rejectedBy = null): void
    {
        if (!$this->status->canBeRejected()) {
            throw new \InvalidArgumentException('Inquiry cannot be rejected in current status');
        }

        $this->changeStatus(InquiryStatus::rejected(), $reason ?: 'Quote rejected', $rejectedBy);
    }

    public function withdraw(?string $reason = null, ?string $withdrawnBy = null): void
    {
        if (!$this->status->canBeWithdrawn()) {
            throw new \InvalidArgumentException('Inquiry cannot be withdrawn in current status');
        }

        $this->changeStatus(InquiryStatus::withdrawn(), $reason ?: 'Withdrawn by customer', $withdrawnBy);
    }

    public function expire(): void
    {
        if (!$this->status->isQuoted()) {
            throw new \InvalidArgumentException('Only quoted inquiries can expire');
        }

        $this->changeStatus(InquiryStatus::expired(), 'Quote expired');
    }

    public function isExpired(): bool
    {
        return $this->expiresAt && $this->expiresAt < new \DateTimeImmutable();
    }

    public function updateNotes(string $notes): void
    {
        $this->notes = $notes;
        $this->markAsUpdated();
    }

    private function changeStatus(InquiryStatus $newStatus, ?string $reason = null, ?string $changedBy = null): void
    {
        if ($this->status->equals($newStatus)) {
            return;
        }

        if (!$this->status->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot transition from %s to %s',
                $this->status->getValue(),
                $newStatus->getValue()
            ));
        }

        $this->status = $newStatus;
        $this->markAsUpdated();
    }

    private function markAsUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}