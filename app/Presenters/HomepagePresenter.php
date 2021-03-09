<?php

namespace App\Presenters;

use App\Entities\Demography;
use App\Entities\Vaccination;
use App\Entities\VaccinationGroup;
use App\Services\Covid19Mzcr;
use App\Services\DataManager;
use Nette\Utils\DateTime;
use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    public function __construct()
    {
        parent::__construct();
    }

    public function renderDefault($date)
    {
        $bulkData = DataManager::getVaccination();
        $vaccinationGroup = new VaccinationGroup();
        $vaccinationGroup->init($bulkData, DateTime::from($date));
        $sumAll = $vaccinationGroup->sumAll($vaccinationGroup->modified);
        $this->template->modified = $sumAll->modified;
        $this->template->source = $sumAll->source;
        $this->template->prvnich_davek = $sumAll->data[0]->prvnich_davek;
        $this->template->druhych_davek = $sumAll->data[0]->druhych_davek;
        $this->template->celkem_davek = $sumAll->data[0]->prvnich_davek + $sumAll->data[0]->druhych_davek;

        $groupByAgeCategory = $vaccinationGroup->groupByAgeCategory($vaccinationGroup->modified);
        $this->template->groupByAgeCategory = $groupByAgeCategory->data;
        $this->template->dateList = $vaccinationGroup->getDateList();
        $this->template->date = $date;
        $this->template->allPeople = Demography::countPeopleExceptOf(Demography::CATEGORY_0_17)
            - $sumAll->data[0]->druhych_davek
            + $groupByAgeCategory->data[VaccinationGroup::CATEGORY_0_17]->druhych_davek;
        $this->template->demography = Demography::countPeopleByAgeCategories();
    }
}
