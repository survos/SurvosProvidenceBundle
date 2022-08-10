<?php

namespace Survos\Providence\XmlModel;

class XmlProfile
{
    use XmlAttributesTrait;

    public $profileName;
    public $profileDescription;
    public ProfileLists $lists;
    public $locales = [];
    public $useForConfiguration;
    public $infoUrl;
    public $base;
    public ProfileElementSets $elementSets;
    public ?ProfileUserInterfaces $userInterfaces = null;
    public ?ProfileRelationshipTable $relationshipTable = null;
    public ?ProfileRelationshipTypes $relationshipTypes;
    public ?ProfileDisplays $displays = null;
    public $searchForms;
    public $logins;
    public $roles;

    public $metadataAlerts;

    /** @returns ProfileList[] */
    public function getLists(): array { return $this->lists->list; }

    /** @returns ProfileMetaDataElement[] */
    public function getElements(): array { return $this->elementSets->metadataElement; }

    /** @returns ProfileDisplay[] */
    public function getDisplays(): array { return $this->displays ? $this->displays->display : []; }

    /** @returns ProfileUserInterface[] */
    public function getUserInterfaces(): array { return $this->userInterfaces ? $this->userInterfaces->userInterface: []; }

    /** @returns ProfileRelationshipTable[] */
    public function getRelationshipTypes(): array { return $this->relationshipTypes ? $this->relationshipTypes->relationshipTable: []; }

    public function ElementsByRestriction(): array {
        $summary = [];
        /** @var ProfileMetaDataElement $element */
        foreach ($this->getElements() as $element) {
            foreach ($element->getTypeRestrictions() as $typeRestriction) {
                $summary[$typeRestriction->table][] = $element;
            }
        }
        return $summary;
    }

//    /** @returns ProfileElementSets[] */
//    public function getElementSets(): array { return $this->elementSets ? $this->elementSets: []; }

//<relationshipTypes>
//<relationshipTable name="ca_objects_x_entities">
//<types>
//<type code="assessor" default="1">

//    /** @returns ProfileRelationshipTable */
//    public function getRelationshipTable(): ?ProfileRelationshipTable  { return $this->relationshipTable ? $this->relationshipTypes->relationshipTable: null; }

    /** @returns ProfileRelationshipTableType[] */
    public function getRelationshipTables(): array  {
        return $this->relationshipTypes ? $this->relationshipTypes->relationshipTable: []; }

    public function getRelationshipTableByCode($code): ProfileRelationshipTable  {
        return current(array_filter($this->getRelationshipTables(), fn(ProfileRelationshipTable $table) => $table->name === $code)); }

    /** @returns ProfileMetaDataElement[] */
    public function getElementsByCode(): array {
        static $mdes;
        if (empty($mdes)) {
            $mdes  = [];
            // @todo: recurse over all MDE's
            /** @var ProfileMetaDataElement $mde */
            foreach ($this->getElements() as $mde) {
                $mdes[strtolower($mde->code)] = $mde;
            }
        }
        return $mdes;
    }

    public function getListsByCode()
    {
        static $lists = [];
        if (empty($lists)) {
            $lists  = [];
            foreach ($this->getLists() as $list) {
                $lists[$list->code] = $list;
            }
        }
        return $lists;
    }

    public function getElementByCode($code) {
        $code = strtolower(str_replace('ca_attribute_', '', $code));
        return $this->getElementsByCode()[$code] ?? null;
    }

    public function getListByCode($code) {
        return $this->getListsByCode()[$code] ?? null;
    }

}
