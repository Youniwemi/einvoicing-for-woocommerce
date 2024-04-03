<?php

namespace Easybill\ZUGFeRD211\Model;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;

class LegalOrganization
{
    /**
     * @Type("Easybill\ZUGFeRD211\Model\Id")
     * @XmlElement(cdata = false, namespace = "urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100")
     * @SerializedName("ID")
     */
    public Id $id;


    /**
     * @Type("string")
     * @XmlElement(cdata=false, namespace="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100")
     * @SerializedName("TradingBusinessName")
     */
    public ?string $tradingBusinessName;


    public static function create(string $id, ?string $schemeID = null, ?string $tradingBusinessName = null): self
    {
        $self = new self();
        $self->id = Id::create($id, $schemeID);
        $self->tradingBusinessName = $tradingBusinessName;
        return $self;
    }
}
