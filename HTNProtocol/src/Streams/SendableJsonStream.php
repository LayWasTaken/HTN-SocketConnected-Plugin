<?php
namespace HTNProtocol\Streams;

final class SendableJsonStream extends JsonStream {
    public string|array $to;
    /**
     * @var Receivable $data
     */

    public function __construct(object $receivable, string $data_type, string $to, string $api = null, int $id = null)
    {
        parent::__construct($receivable, $data_type, $api, $id);
        $this->to = $to;
    }
}