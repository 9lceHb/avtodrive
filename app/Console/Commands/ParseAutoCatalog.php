<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Orchestra\Parser\Xml\Facade as XmlParser;
use App\Models\Car;
use Carbon\Carbon;

class ParseAutoCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parsing:parse-auto-catalog {pathToFile=storage/dataForParsing/data.xml}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parsing auto-catalog xml file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Парсинг xml приведение к массиву.
        $pathToFile = $this->argument('pathToFile');
        try {
            $carsXml = XmlParser::load($this->argument('pathToFile'));
            $cars = $carsXml->parse([
                'cars' => ['uses' => 'offers.offer[id>external_id,mark,model,generation,year,run,color,body-type>body_type,engine-type>engine_type,transmission,gear-type>gear_type,generation_id]'],
            ])['cars'];
        } catch (\Throwable $e) {
            Log::error("Ошибка парсинга файла {$pathToFile}", ['error' => $e]);
            throw new \Exception('Ошибка парсинга файла');
        }

        try {
            // Выборка пришедших id, для удаления из БД всех cars не из списка.
            $external_ids = array_column($cars, 'external_id');
            Car::whereNotIn('external_id', $external_ids)->delete();

            // Создание или обновление остальных данных
            Car::upsert($cars, 'external_id');
            Log::info("Файл {$pathToFile} обработан успешно.");
        } catch (\Throwable $e) {
            Log::error("Ошибка обновления данных файла {$pathToFile} в БД", ['error' => $e]);
            throw new \Exception('Ошибка сохранинеия данных');
        }

    }
}
