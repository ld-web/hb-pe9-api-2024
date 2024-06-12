<?php

namespace App\Request;

class RequestUri
{
    public const OPERATION_COLLECTION = "COLLECTION";
    public const OPERATION_ITEM = "ITEM";

    private array $uriSegments = [];

    public function __construct(
        private string $originalUri
    ) {
        $this->uriSegments = explode('/', ltrim($originalUri, '/'));
    }

    public function getOriginalUri(): string
    {
        return $this->originalUri;
    }

    public function getUriSegments(): array
    {
        return $this->uriSegments;
    }

    public function getResourceName(): ?string
    {
        return $this->uriSegments[0] ?? null;
    }

    public function getIdentifier(): ?int
    {
        if (isset($this->uriSegments[1])) {
            return intval($this->uriSegments[1]);
        }

        return null;
    }

    public function getOperationType(): ?string
    {
        $uriSegmentsCount = count($this->uriSegments);

        if ($uriSegmentsCount === 1) {
            return self::OPERATION_COLLECTION;
        }

        if ($uriSegmentsCount === 2) {
            return self::OPERATION_ITEM;
        }

        return null;
    }
}
