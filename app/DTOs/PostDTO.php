<?php

namespace App\DTOs;

class PostDTO
{
    public function __construct(
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $rawContent,
        public readonly ?string $priceText,
        public readonly ?float $priceValue,
        public readonly ?string $areaText,
        public readonly ?float $areaValue,
        public readonly array $frontageTexts,
        public readonly int $frontageCount,
        public readonly ?string $depthText,
        public readonly ?string $addressText,
        public readonly ?string $ward,
        public readonly ?string $district,
        public readonly ?string $city,
        public readonly ?string $propertyType,
        public readonly array $phones,
        public readonly array $features,
        public readonly ?string $facebookUrl,
        public readonly ?float $confidence,
        public readonly ?string $imageUrl,
        public readonly array $images,
        public readonly ?string $authorId,
        public readonly ?string $authorName,
        public readonly ?string $sourceGroup,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            rawContent: $data['raw_content'] ?? null,
            priceText: $data['price_text'] ?? null,
            priceValue: isset($data['price_value']) ? (float) $data['price_value'] : null,
            areaText: $data['area_text'] ?? null,
            areaValue: isset($data['area_value']) ? (float) $data['area_value'] : null,
            frontageTexts: $data['frontage_texts'] ?? [],
            frontageCount: (int) ($data['frontage_count'] ?? 0),
            depthText: $data['depth_text'] ?? null,
            addressText: $data['address_text'] ?? null,
            ward: $data['ward'] ?? null,
            district: $data['district'] ?? null,
            city: $data['city'] ?? null,
            propertyType: $data['property_type'] ?? null,
            phones: $data['phones'] ?? [],
            features: $data['features'] ?? [],
            facebookUrl: $data['facebook_url'] ?? null,
            confidence: isset($data['confidence']) ? (float) $data['confidence'] : null,
            imageUrl: $data['image_url'] ?? null,
            images: $data['images'] ?? [],
            authorId: $data['author_id'] ?? null,
            authorName: $data['author_name'] ?? null,
            sourceGroup: $data['source_group'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'raw_content' => $this->rawContent,
            'price_text' => $this->priceText,
            'price_value' => $this->priceValue,
            'area_text' => $this->areaText,
            'area_value' => $this->areaValue,
            'frontage_texts' => $this->frontageTexts,
            'frontage_count' => $this->frontageCount,
            'depth_text' => $this->depthText,
            'address_text' => $this->addressText,
            'ward' => $this->ward,
            'district' => $this->district,
            'city' => $this->city,
            'property_type' => $this->propertyType,
            'phones' => $this->phones,
            'features' => $this->features,
            'facebook_url' => $this->facebookUrl,
            'confidence' => $this->confidence,
            'image_url' => $this->imageUrl,
            'images' => $this->images,
            'author_id' => $this->authorId,
            'author_name' => $this->authorName,
            'source_group' => $this->sourceGroup,
        ];
    }
}
