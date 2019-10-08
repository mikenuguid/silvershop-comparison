<?php

namespace SilverShop\Comparison\Model;

use SilverShop\Page\Product;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\ORM\FieldType\DBFloat;
use SilverStripe\ORM\FieldType\DBVarchar;


class Feature extends DataObject {

    private static $db = [
        'Title' => 'Varchar',
        'Unit' => 'Varchar',
        'ValueType' => "Enum('Boolean,Number,String','String')"
    ];

    private static $has_many = array(
        "Products" => ProductFeatureValue::class
    );

    private static $has_one = array(
        "Group" => FeatureGroup::class
    );

    private static $belongs_many_many = array(
        "Product" => Product::class
    );

    private static $summary_fields = array(
        "Title" => "Title",
        "Unit" => "Unit"
    );

    private static $table_name = 'SilverShop_Feature';

    private static $singular_name = "Feature";

    private static $plural_name = "Features";

    public function getCMSFields() {
        $fields = new FieldList(
            TextField::create("Title"),
            TextField::create("Unit"),
            DropdownField::create("ValueType","Value Type", $this->dbObject('ValueType')->enumValues())
        );

        $groups = FeatureGroup::get();

        if($groups->exists()) {
            $fields->insertAfter(
                DropdownField::create("GroupID","Group",$groups->map()->toArray())
                    ->setHasEmptyDefault(true)
            ,"Unit");
        }

        $this->extend('updateCMSFields',$fields);

        return $fields;
    }

    public function summaryFields() {
        $fields = parent::summaryFields();

        if(FeatureGroup::get()->exists()){
            $fields['Group.Title'] = 'Group';
        }

        return $fields;
    }

    public function getValueField() {
        $fields = array(
            'Boolean' => CheckboxField::create("Value"),
            'Number' => NumericField::create("Value"),
            'String' => TextField::create("Value")
        );

        if(isset($fields[$this->ValueType])) {
            return $fields[$this->ValueType];
        } else {
            return new LiteralField("Value", _t('Feature.SAVETOADDVALUE', 'Save record to add value.'));
        }
    }

    public function getValueDBField($value) {
        $fields = array(
            'Boolean' => new DBBoolean(),
            'Number' => new DBFloat(),
            'String' => new DBVarchar()
        );
        $field =  $fields[$this->ValueType];
        $field->setValue($value);

        return $field;
    }

}
