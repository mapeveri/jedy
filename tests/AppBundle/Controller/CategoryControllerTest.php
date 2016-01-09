<?php

namespace Test\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    private $name = 'TestCategory';

    public function testIndexAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/admin/categories/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Categories', $crawler->filter('h1.h-btn-line')->text());
    }

    /**
     * Creation a new Category without parent category
     */
    public function testNewAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/admin/category/new/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $buttonCrawler = $crawler->selectButton('Add category')->form();

        //Error validation
        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertRegExp(
            '/This value should not be blank./',
            $client->getResponse()->getContent()
        );

        //Good
        $buttonCrawler['category_form[name]'] = $this->name;

        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->isRedirect());
        $client->followRedirect();

        $this->assertContains(
            'created_successfully',
            $client->getResponse()->getContent()
        );
    }

    /**
     * Validation HasTranslationParent category error
     */
    public function testNewParentErrorAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/admin/category/new/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $buttonCrawler = $crawler->selectButton('Add category')->form();

        $idSelect = $crawler->filter('#category_form_parent option')->last()->attr('value');

        $buttonCrawler['category_form[name]'] = 'SubCategory of '.$this->name;
        $buttonCrawler['category_form[parent]'] = $idSelect;

        $client->submit($buttonCrawler);

        $this->assertRegExp(
            '/You must create the translations of the parent category/',
            $client->getResponse()->getContent()
        );
    }

    /**
     * Count translations of a category
     */
    public function testTranslationsAction()
    {
        $client = static::createClient();

        $route = "/en/admin/category/".$this->selectCategoryByName($this->name)->getId()."/translations/";
        $client->request('GET', $route);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    /**
     * add translation to category in English
     */
    public function testAddTranslationEnAction()
    {
        $client = static::createClient();
        $routeEn = "en/admin/category/".$this->selectCategoryByName($this->name)->getId()."/translations/add/es/en";

        //English
        $crawler = $client->request('GET', $routeEn);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $buttonCrawler = $crawler->selectButton('Add translation category')->form();

        $buttonCrawler['category_form[name]'] = $this->name."En";

        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->isRedirect());
        $client->followRedirect();

        $this->assertContains(
            'created_successfully',
            $client->getResponse()->getContent()
        );
    }

    /**
     * add translation to category in French
     */
    public function testAddTranslationFrAction()
    {
        $client = static::createClient();
        $routeFr = "en/admin/category/".$this->selectCategoryByName($this->name)->getId()."/translations/add/es/fr";

        //French
        $crawler = $client->request('GET', $routeFr);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $buttonCrawler = $crawler->selectButton('Add translation category')->form();

        $buttonCrawler['category_form[name]'] = $this->name."Fr";

        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->isRedirect());
        $client->followRedirect();

        $this->assertContains(
            'created_successfully',
            $client->getResponse()->getContent()
        );
    }

    /**
     * Create a translation parent category ok
     */
    public function testNewParentOkAction()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/en/admin/category/new/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $buttonCrawler = $crawler->selectButton('Add category')->form();

        $idSelect = $crawler->filter('#category_form_parent option')->last()->attr('value');

        $buttonCrawler['category_form[name]'] = 'SubCategory of '.$this->name;
        $buttonCrawler['category_form[parent]'] = $idSelect;

        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->isRedirect());
        $client->followRedirect();

        $this->assertContains(
            'created_successfully',
            $client->getResponse()->getContent()
        );
    }

    /**
     * Edit translation english
     */
    public function testEditTranslationAction()
    {
        $client = static::createClient();
        $route = "/en/admin/category/".$this->selectCategoryByName($this->name)->getId(
            )."/translations/".$this->selectCategoryByName($this->name."En")->getId()."/edit/es/en";

        $crawler = $client->request('GET', $route);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $buttonCrawler = $crawler->selectButton('Edit category')->form();

        $buttonCrawler['category_form[name]'] = "Edit En";
        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->isRedirect());
        $client->followRedirect();

        $this->assertContains(
            'created_successfully',
            $client->getResponse()->getContent()
        );
    }

    /**
     * Test Edit Category
     */
    public function testEditAction()
    {
        $client = static::createClient();
        $route = "/en/admin/category/".$this->selectCategoryByName($this->name)->getId()."/edit/";
        $crawler = $client->request('GET', $route);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $buttonCrawler = $crawler->selectButton('Edit category')->form();
        $buttonCrawler['category_form[name]'] = $this->name."Edit";

        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->isRedirect());
        $client->followRedirect();

        $this->assertContains(
            'created_successfully',
            $client->getResponse()->getContent()
        );
    }

    public function testDeleteCategory()
    {
        $client = static::createClient();
        $route = "/en/admin/category/".$this->selectCategoryByName($this->name."Edit")->getId()."/edit/";
        $crawler = $client->request('GET', $route);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $buttonCrawler = $crawler->selectButton('Delete')->form();
        $client->submit($buttonCrawler);

        $this->assertEquals(200, $client->getResponse()->isRedirect());
        $client->followRedirect();

        $this->assertContains(
            'admin_category_home.',
            $client->getResponse()->getContent()
        );
    }

    /**
     * @return mixed
     */
    private function selectCategoryByName($name)
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        return $this->em->getRepository('AppBundle:Category')->findOneBy(['name' => $name]);
    }
}