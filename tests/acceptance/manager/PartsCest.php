<?php

namespace hipanel\modules\stock\tests\acceptance\manager;

use hipanel\helpers\Url;
use Codeception\Example;
use hipanel\tests\_support\Page\IndexPage;
use hipanel\modules\stock\tests\_support\Page\part\SellModalWindow;
use hipanel\tests\_support\Page\Widget\Input\Dropdown;
use hipanel\tests\_support\Page\Widget\Input\Input;
use hipanel\modules\stock\tests\_support\Page\part\Create;
use hipanel\tests\_support\Step\Acceptance\Manager;

class PartsCest
{

    protected Create $createPage;
    protected $testOrderData;
    protected array $sellData;

    public function _before(Manager $I): void
    {
        $this->createPage = new Create($I);
    }

    public function ensurePartsPageWorks(Manager $I): void
    {
        $I->login();
        $I->needPage(Url::to('@part'));
    }

    /**
     * Checks work of part form buttons (add, copy, remove).
     *
     * @param Manager $I
     * @throws \Exception
     */
    public function ensurePartManageButtonsWorks(Manager $I): void
    {
        $page = $this->createPage;
        $I->needPage(Url::to('@part/create'));

        $n = 0;

        $I->seeNumberOfElements('div.item', ++$n);

        $page->addPart();
        $I->seeNumberOfElements('div.item', ++$n);

        $page->addPart();
        $I->seeNumberOfElements('div.item', ++$n);

        $page->copyPart();
        $I->seeNumberOfElements('div.item', ++$n);

        $page->removePart();
        $I->seeNumberOfElements('div.item', --$n);

        $page->removePart();
        $I->seeNumberOfElements('div.item', --$n);

        $page->removePart();
        $I->seeNumberOfElements('div.item', --$n);
    }

    /**
     * Tries to create a new single part without any data.
     *
     * Expects error due blank fields.
     *
     * @param Manager $I
     * @throws \Exception
     */
    public function ensureICantCreatePartWithoutData(Manager $I): void
    {
        $page = $this->createPage;

        $I->needPage(Url::to('@part/create'));
        $I->pressButton('Save');

        $page->containsBlankFieldsError([
            'Part No.',
            'Source',
            'Destination',
            'Serials',
            'Move description',
            'Purchase price',
            'Currency'
        ]);
    }

    /**
     * Tries to create a new single part.
     *
     * Expects successful part creation.
     *
     * @param Manager $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function ensureICanCreatePart(Manager $I): void
    {
        $I->needPage(Url::to('@part/create'));
        $page = $this->createPage;
        $page->fillPartFields($this->getPartData());
        $page->pressSaveButton();
        $page->seePartWasCreated();
    }

    /**
     * Tries to create several parts.
     *
     * Expects successful parts creation.
     *
     * @param Manager $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function ensureICanCreateSeveralParts(Manager $I): void
    {
        $page = $this->createPage;

        $I->needPage(Url::to('@part/create'));
        $page->fillPartFields($this->getPartData());
        $page->addPart($this->getPartData());
        $page->pressSaveButton();
        $page->seePartWasCreated();
    }

    /**
     * Create and delete new parts
     *
     * @param Manager $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function ensureICanCreateAndTrashPart(Manager $I): void
    {
        $page = $this->createPage;

        $I->needPage(Url::to('@part/create'));
        $page->fillPartFields($this->getPartData());
        $page->pressSaveButton();
        $page->seePartWasCreated();

        $I->click("//a[contains(text(), 'Delete')]");
        $I->acceptPopup();
        $I->closeNotification('Part has been deleted');
    }

    /**
     * @dataProvider getSellData
     */
    public function ensureICanSellParts(Manager $I, Example $example): void
    {
        $partIndex      = new IndexPage($I);
        $I->needPage(Url::to('@part'));
        $this->sellData = iterator_to_array($example->getIterator());

        $partIndex->filterBy(Input::asTableFilter($I, 'Serial'), 'MG_TEST_PART');
        for ($i = 0; $i < count($this->sellData['prices']); $i++) {
            $partIndex->selectTableRowByNumber($i + 1);
        }
        $I->click("//button[contains(text(), 'Sell parts')]");
        $I->click("//a[text()='Sell parts']");
        $I->waitForPageUpdate();

        $sellModal      = new SellModalWindow($I);
        $sellModal->fillSellWindowFields($this->sellData);
        $I->pressButton('Sell');
        $sellModal->seePartsWereSold();
    }

    public function ensureSellingBillWasCreated(Manager $I): void
    {
        $billPage = new IndexPage($I);

        $I->needPage(Url::to('@bill'));

        $this->filterTable($I);
        $billPage->openRowMenuByNumber(1);
        $billPage->chooseRowMenuOption('View');

        $I->seeNumberOfElements('tr table  tr[data-key]', count($this->sellData['prices']));
    }

    protected function filterTable(Manager $I): void
    {
        $billPage = new IndexPage($I);

        $billPage->filterBy(Dropdown::asTableFilter($I, 'Type'),
            '-- ' . $this->sellData['type']);

        $billPage->filterBy(Input::asTableFilter($I, 'Description'),
            $this->sellData['descr']);
    }

    /**
     * @return array
     */
    protected function getPartData(): array
    {
        return [
            'partno'        => 'CHASSIS EPYC 7402P',
            'src_id'        => 'TEST-DS-01',
            'dst_id'        => 'TEST-DS-02',
            'serials'       => 'MG_TEST_PART' . uniqid(),
            'move_descr'    => 'MG TEST MOVE',
            'price'         => 200,
            'currency'      => 'usd',
            'company_id'    => 'Other'
        ];
    }

    protected function getSellData(): array
    {
        return [
            'sellInfo' => [
                'contact_id'=> 'Test Manager',
                'currency'  => 'eur',
                'descr'     => 'test description ' . uniqid(),
                'type'      => 'HW purchase',
                'prices'    => [250, 300, 442],
                'time'      => (new \DateTime())->format('Y-m-d H:i'),
            ]
        ];
    }
}