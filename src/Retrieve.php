<?php

declare(strict_types=1);

namespace Rechtlogisch\TseId;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Throwable;

class Retrieve
{
    private int $countPages = 1;

    private const URL = 'https://www.bsi.bund.de/EN/Themen/Unternehmen-und-Organisationen/Standards-und-Zertifizierung/Zertifizierung-und-Anerkennung/Listen/Zertifizierte-Produkte-nach-TR/Technische_Sicherheitseinrichtungen/TSE_node.html?gts=913608_list%253Dtitle_text_sort%252Bdesc&gtp=913608_list%253D';

    /**
     * @var array<string, array<string, string>>
     */
    private array $retrieved = [];

    public function __construct()
    {
        $this->run();
    }

    public function run(): void
    {
        $page = 1;

        while ($page <= $this->countPages) {
            $this->page($page);
            $page++;
        }

        krsort($this->retrieved, SORT_NATURAL);
    }

    public function page(int $no = 1): void
    {
        $url = self::URL.$no;

        $browser = new HttpBrowser(HttpClient::create());
        $crawler = $browser->request('GET', $url);

        if ($no === 1) {
            $paginationText = $crawler->filter('#content nav.c-pagination p')->text();
            preg_match('/Search results (\d+) to (\d+) from a total of (\d+)/', $paginationText, $matches);
            $max = $matches[3] ?? '0';

            $this->countPages = (int) ceil((int) $max / 10);
        }

        $crawler->filter('#content div.wrapperTable table.textualData tbody tr')->each(function (Crawler $row) {
            $rowData = [];
            $tseId = null;

            $row->filter('td')->each(function (Crawler $cell, int $index) use (&$rowData, &$tseId) {
                $header = '';

                switch ($index) {
                    case 0: $header = 'tse_id';
                        break;
                    case 1: $header = 'content';
                        break;
                    case 2: $header = 'manufacturer';
                        break;
                    case 3: $header = 'date_issuance';
                        break;
                }

                if ($header === 'tse_id') {
                    $fullIdText = $cell->text();
                    $tseId = str_replace('BSI-K-TR-', '', $fullIdText);
                    [$id, $year] = explode('-', $tseId);
                    $rowData['id'] = $id;
                    $rowData['year'] = $year;
                } else {
                    $rowData[$header] = trim($cell->text());
                }
            });

            $this->retrieved[$tseId] = $rowData;
        });
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function list(): array
    {
        return $this->retrieved;
    }

    public function json(?string $tseId = null, bool $pretty = true): string
    {
        try {
            $retrieved = ($tseId) ? [$tseId => $this->retrieved[$tseId]] : $this->retrieved;

            $flags = JSON_THROW_ON_ERROR;
            if ($pretty) {
                $flags |= JSON_PRETTY_PRINT;
            }

            return json_encode($retrieved, $flags);
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array<string, string>
     */
    public function save(mixed $path = '.'): array
    {
        $files = [];

        $prefix = date('Y-m-d');

        $files['json'] = $this->saveJson($path.DIRECTORY_SEPARATOR.$prefix); // @phpstan-ignore-line

        return $files;
    }

    private function saveJson(string $path): string
    {
        $content = $this->json();
        $pathWithExtension = $path.'.json';
        $result = file_put_contents($pathWithExtension, $content);

        if ($result === false) {
            return '';
        }

        $path = realpath($pathWithExtension);

        if ($path === false) {
            return '';
        }

        return $path;
    }
}
