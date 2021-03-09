<?php

namespace App\Entities;

use Exception;
use Nette\Utils\DateTime;
use stdClass;

class VaccinationGroup
{
    const CATEGORY_0_17 = 0;

    public $modified;
    public $source;
    /** @var Vaccination[] */
    public $data = [];
    public $dateList = [];

    /**
     * Fill in object with data from Covid19 API
     * up to given date
     * Create list for date selector
     *
     * @param stdClass $bulkData data from Covid19 API
     * @throws Exception
     */
    public function init(stdClass $bulkData)
    {
        $this->data = [];
        $this->modified = DateTime::from($bulkData->modified);
        $this->source = $bulkData->source;

        foreach (array_reverse($bulkData->data) as $vaccination) {
            $day = DateTime::from($vaccination->datum)->format('Y-m-d');
            $month = DateTime::from($vaccination->datum)->format('m');
            $year = DateTime::from($vaccination->datum)->format('Y');
            $this->dateList[$year][$month][$day] = $day;
            $this->data[] = $vaccination;
        }
    }

    /**
     * Compute vaccination sums up to given date:
     * - first administration of the vaccine
     * - second administration of the vaccine
     * - both administration of the vaccine
     * @param DateTime $date
     * @return VaccinationGroup
     * @throws Exception
     */
    public function sumAll(DateTime $date): VaccinationGroup
    {
        $group = new VaccinationGroup();
        $group->modified = $date;
        $group->source = $this->source;
        $group->data[] = new Vaccination();
        foreach ($this->data as $vaccination) {
            if (DateTime::from($vaccination->datum) <= $date) {
                $group->data[0]->prvnich_davek += $vaccination->prvnich_davek;
                $group->data[0]->druhych_davek += $vaccination->druhych_davek;
                $group->data[0]->celkem_davek += $vaccination->celkem_davek;
            }
        }

        return $group;
    }

    /**
     * Compute sums in age categories up to given date
     * @param DateTime $date
     * @return VaccinationGroup
     * @throws Exception
     */
    public function groupByAgeCategory(DateTime $date): VaccinationGroup
    {
        function cmp($a, $b): int
        {
            if ($a->vekova_skupina == $b->vekova_skupina) {
                return 0;
            }
            return ($a->vekova_skupina < $b->vekova_skupina) ? -1 : 1;
        }

        $group = new VaccinationGroup();
        $group->modified = $date;
        $group->source = $this->source;
        foreach ($this->data as $vaccination) {
            if (DateTime::from($vaccination->datum) <= $date) {
                if (!key_exists($vaccination->vekova_skupina, $group->data)) {
                    $group->data[$vaccination->vekova_skupina] = new Vaccination();
                }
                $group->data[$vaccination->vekova_skupina]->datum = $group->data;
                $group->data[$vaccination->vekova_skupina]->vekova_skupina = $vaccination->vekova_skupina;
                $group->data[$vaccination->vekova_skupina]->prvnich_davek += $vaccination->prvnich_davek;
                $group->data[$vaccination->vekova_skupina]->druhych_davek += $vaccination->druhych_davek;
                $group->data[$vaccination->vekova_skupina]->celkem_davek += $vaccination->celkem_davek;
            }
        }
        usort($group->data, 'App\\Entities\\cmp');

        return $group;
    }

    /**
     * Return list of all dates for date selector
     * @return array
     */
    public function getDateList(): array
    {
        return $this->dateList;
    }

}

