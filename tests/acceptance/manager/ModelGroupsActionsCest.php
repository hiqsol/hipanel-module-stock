<?php

namespace hipanel\modules\stock\tests\acceptance\manager;

use hipanel\helpers\Url;
use hipanel\tests\_support\Page\IndexPage;
use hipanel\tests\_support\Page\Widget\Input\Input;
use hipanel\tests\_support\Step\Acceptance\Manager;

class ModelGroupsActionsCest
{
    /**
     * @var IndexPage
     */
    private $index;

    public function _before(Manager $I)
    {
        $this->index = new IndexPage($I);
    }

    public function ensureCreateModelGroupsWorks(Manager $I): void
    {
        $I->needPage(Url::to('@model-group/create'));
        $I->pressButton('Save');
        $I->waitForPageUpdate();
        $I->waitForText('Name cannot be blank.');
        $I->click("//button[contains(@class, 'add-item')]");
        $I->click("//button[contains(@class, 'add-item')]");
        foreach (range(0,2) as $i) {
            (new Input($I, "//input[@name='ModelGroup[$i][name]']"))
                ->setValue("TEST_MODEL_GROUP_$i");
            (new Input($I, "//textarea[contains(@name, 'ModelGroup[$i][descr]')]"))
                ->setValue("Test description for $i item");
            (new Input($I, "//input[@name='ModelGroup[$i][limit_dtg]']"))
                ->setValue(($i + 1) * 10);
            (new Input($I, "//input[@name='ModelGroup[$i][limit_sdg]']"))
                ->setValue(($i + 1) * 10);
            (new Input($I, "//input[@name='ModelGroup[$i][limit_m3]']"))
                ->setValue(($i + 1) * 10);
            (new Input($I, "//input[@name='ModelGroup[$i][limit_twr]']"))
                ->setValue(($i + 1) * 10);
        }
        $I->pressButton('Save');
        $I->waitForPageUpdate();
        $I->closeNotification('Created');
        $I->seeInCurrentUrl('/stock/model-group/index');
    }
//
//    public function ensureFilterByNameWorks(Manager $I): void
//    {
//        $name = 'test';
//        $selector = "//input[contains(@name, 'ModelGroupSearch[name_ilike]')]";
//
//        $I->needPage(Url::to('@model-group'));
//        $this->index->filterBy(new Input($I, $selector), $name);
//        $count = $this->index->countRowsInTableBody();
//        for ($i = 1 ; $i <= $count; ++$i) {
//            $I->see($name, "//tbody/tr[$i]");
//        }
//    }

}
