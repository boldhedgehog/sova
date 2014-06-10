<?php
/**
 * User: boldhedgehog
 * Date: 20.08.12
 */

class hostId extends keyDatatype
{
    protected $name = "host_id";
}

class hostName extends stringDatatype
{
    protected $name = "name";
}

class hostNagiosName extends stringDatatype
{
    protected $name = "nagios_name";
}

class hostAlias extends stringDatatype
{
    protected $name = "alias";
}

class hostType extends choiceDatatype
{
    protected $name = "type";
    protected $choices = array('pno' => 'ПНО', 'service' => 'Службовий');
}


class hostSendSosEvent extends booleanDatatype
{
    protected $name = "send_sos_event";
}

class hostDescription extends textDatatype
{
    protected $name = "description";
}

class hostNagvisMapName extends stringDatatype
{
    protected $name = "nagvis_map_name";
}

class hostNagvisThumbName extends stringDatatype
{
    protected $name = "nagvis_thumb_name";
}

class hostIcon extends stringDatatype
{
    protected $name = "icon";
}

class hostLocation extends stringDatatype
{
    protected $name = "location";
}

class hostSearchSystemCode extends stringDatatype
{
    protected $name = "search_system_code";
}

class hostObjectId extends stringDatatype
{
    protected $name = "object_id";
}

class hostLinkerId extends stringDatatype
{
    protected $name = "linker_id";
}

class hostObjectTypeId extends integerDatatype
{
    protected $name = "object_type_id";
}

class hostEmergencyGroupId extends choiceDatatype
{
    protected $name = "emergency_group_id";
    protected $choices = array(1 => "Низька", 2 => "Середня", 3 => "Висока");
}

class hostContactLegalId extends keyDatatype
{
    protected $name = "contact_legal_id";
}

class hostEdrpou extends stringDatatype
{
    protected $name = "edrpou";
}

class hostInn extends stringDatatype
{
    protected $name = "inn";
}

class hostDk009_96 extends stringDatatype
{
    protected $name = "dk009_96";
}

class hostDk002_2004 extends stringDatatype
{
    protected $name = "dk002_2004";
}

class hostCentralAuthority extends stringDatatype
{
    protected $name = "central_authority";
}

class hostLocalAuthority extends stringDatatype
{
    protected $name = "local_authority";
}

class hostIsOnService extends booleanDatatype
{
    protected $name = "is_on_service";
}

class hostSchemeImage extends imageDatatype {
    protected $name = "scheme_image_name";
    protected $caption = "Схема";
}

class hostPassport extends stringDatatype
{
    protected $name = "passport";
}

class hostConfigInfo extends textDatatype
{
    protected $name = "config_info";
}
