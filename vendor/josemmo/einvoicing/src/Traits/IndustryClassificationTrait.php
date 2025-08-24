<?php
namespace Einvoicing\Traits;

trait IndustryClassificationTrait {
    protected $industryClassificationCode = null;
    protected $industryClassificationName = null;

    /**
     * Get industry classification code
     * @return string|null Industry classification code
     */
    public function getIndustryClassificationCode(): ?string {
        return $this->industryClassificationCode;
    }

    /**
     * Set industry classification code
     * @param  string|null $industryClassificationCode Industry classification code
     * @return self                                     Instance
     */
    public function setIndustryClassificationCode(?string $industryClassificationCode): self {
        $this->industryClassificationCode = $industryClassificationCode;
        return $this;
    }

    /**
     * Get industry classification name
     * @return string|null Industry classification name
     */
    public function getIndustryClassificationName(): ?string {
        return $this->industryClassificationName;
    }

    /**
     * Set industry classification name
     * @param  string|null $industryClassificationName Industry classification name
     * @return self                                     Instance
     */
    public function setIndustryClassificationName(?string $industryClassificationName): self {
        $this->industryClassificationName = $industryClassificationName;
        return $this;
    }
}