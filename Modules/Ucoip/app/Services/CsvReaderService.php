<?php

namespace Modules\Ucoip\Services;


class CsvReaderService
{
public function readCsv($file): array
{
    $rows = [];

    if (($handle = fopen($file->getRealPath(), "r")) !== false) {

        stream_filter_append($handle, 'convert.iconv.Windows-1252/UTF-8//IGNORE');
        $headers = fgetcsv($handle);

        while (($data = fgetcsv($handle)) !== false) {
            if (count($headers) === count($data)) {
                $rows[] = array_combine($headers, $data);
            }
        }

        fclose($handle);
    }

    return $rows;
}

}
