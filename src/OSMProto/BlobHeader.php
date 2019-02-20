<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: proto/fileformat.proto

namespace OSMProto;

use Google\Protobuf\Internal\GPBUtil;
use OSMProto\GPBMetadata\Fileformat;

/**
 * Generated from protobuf message <code>OSMReader.BlobHeader</code>
 */
class BlobHeader extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>string type = 1;</code>
     */
    private $type = '';
    /**
     * Generated from protobuf field <code>bytes indexdata = 2;</code>
     */
    private $indexdata = '';
    /**
     * Generated from protobuf field <code>int32 datasize = 3;</code>
     */
    private $datasize = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     * @type string $type
     * @type string $indexdata
     * @type int $datasize
     * }
     */
    public function __construct($data = NULL)
    {
        Fileformat::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>string type = 1;</code>
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Generated from protobuf field <code>string type = 1;</code>
     * @param string $var
     * @return $this
     * @throws \Exception
     */
    public function setType($var)
    {
        GPBUtil::checkString($var, True);
        $this->type = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>bytes indexdata = 2;</code>
     * @return string
     */
    public function getIndexdata()
    {
        return $this->indexdata;
    }

    /**
     * Generated from protobuf field <code>bytes indexdata = 2;</code>
     * @param string $var
     * @return $this
     * @throws \Exception
     */
    public function setIndexdata($var)
    {
        GPBUtil::checkString($var, False);
        $this->indexdata = $var;

        return $this;
    }

    /**
     * Generated from protobuf field <code>int32 datasize = 3;</code>
     * @return int
     */
    public function getDatasize()
    {
        return $this->datasize;
    }

    /**
     * Generated from protobuf field <code>int32 datasize = 3;</code>
     * @param int $var
     * @return $this
     * @throws \Exception
     */
    public function setDatasize($var)
    {
        GPBUtil::checkInt32($var);
        $this->datasize = $var;

        return $this;
    }

}
