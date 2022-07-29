<?php
namespace HTNProtocol\Streams;

final class ReceivedJsonStream extends JsonStream {
    public string $from;
    /**
     * @var Receivable $data
     */

    public function __construct(Receivable $receivable, string $data_type, string $from, string $api = null, int $id = null)
    {
        parent::__construct($receivable, $data_type, $api, $id);
        $this->from = $from;
    }
}