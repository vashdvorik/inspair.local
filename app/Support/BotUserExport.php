<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\BotUser;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Выгрузка всех участников файлом Excel (.xlsx) для администраторов. В файле только то, что спрашивает бот при регистрации.
 * Ширина колонок подбирается под самые длинные значения, текст переносится по строкам, шапка выделена жирным шрифтом.
 */
class BotUserExport
{
    private const HEADERS = ['Имя и фамилия', 'Университет/организация и должность', 'Телефон', 'Email', 'Город'];

    /** Самая узкая и самая широкая колонка в символах: длинные значения переносятся на следующую строку внутри ячейки. */
    private const MIN_WIDTH = 14;
    private const MAX_WIDTH = 60;

    public function download(): StreamedResponse
    {
        $name = 'participants-'.now()->format('Y-m-d-His').'.xlsx';

        return response()->streamDownload(function (): void {
            $path = tempnam(sys_get_temp_dir(), 'participants');
            $this->writeTo($path);
            readfile($path);
            @unlink($path);
        }, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Записывает файл по указанному пути. */
    public function writeTo(string $path): void
    {
        $rows = $this->rows();

        $options = new Options();
        foreach ($this->widths($rows) as $i => $width) {
            $options->setColumnWidth($width, $i + 1);
        }

        $writer = new Writer($options);
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Участники');

        $wrap = (new Style())->setShouldWrapText(true);
        $head = (new Style())->setFontBold(true)->setShouldWrapText(true);

        $writer->addRow(Row::fromValues(self::HEADERS, $head));
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row, $wrap));
        }

        $writer->close();
    }

    /** @return array<int, array<int, string>> */
    public function rows(): array
    {
        $rows = [];

        BotUser::query()->orderBy('id')->chunkById(500, function ($users) use (&$rows): void {
            foreach ($users as $user) {
                $rows[] = $this->row($user);
            }
        });

        return $rows;
    }

    /** @return array<int, string> */
    public function row(BotUser $u): array
    {
        return array_map(
            static fn (mixed $v): string => trim(str_replace(["\r\n", "\r", "\n"], ' ', (string) ($v ?? ''))),
            [$u->full_name, $u->organization, $u->contact_phone, $u->contact_email, $u->city],
        );
    }

    /**
     * Ширина каждой колонки в символах по самому длинному значению (с запасом), не уже и не шире заданных пределов.
     *
     * @param  array<int, array<int, string>>  $rows
     * @return array<int, float>
     */
    public function widths(array $rows): array
    {
        $widths = [];
        foreach (self::HEADERS as $i => $header) {
            $longest = mb_strlen($header);
            foreach ($rows as $row) {
                $longest = max($longest, mb_strlen($row[$i] ?? ''));
            }
            $widths[$i] = (float) min(self::MAX_WIDTH, max(self::MIN_WIDTH, $longest + 3));
        }

        return $widths;
    }
}
