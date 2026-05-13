<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Company;
use App\Entity\Review;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReviewControllerTest extends WebTestCase
{
    private static bool $schemaReady = false;

    public static function setUpBeforeClass(): void
    {
        $kernel = static::bootKernel();
        /** @var EntityManagerInterface $em */
        $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        self::$schemaReady = true;
        static::ensureKernelShutdown();
    }

    // ── Route tests ───────────────────────────────────────────────

    public function testReviewListReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('h1');
    }

    public function testNewFormPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/new');
        self::assertResponseIsSuccessful();
    }

    public function testShowReturns404ForMissingReview(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/99999');
        self::assertResponseStatusCodeSame(404);
    }

    public function testCompanyStatsPageReturns200(): void
    {
        $client = static::createClient();
        $client->request('GET', '/companies');
        self::assertResponseIsSuccessful();
    }

    // ── Form submission ───────────────────────────────────────────

    public function testSubmitValidReviewCreatesAndRedirects(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/new');

        $client->submitForm('Vélemény beküldése', [
            'review[companyName]' => 'Teszt Kft',
            'review[rating]' => '4',
            'review[reviewText]' => 'Kiváló szolgáltatás, nagyon ajánlom mindenkinek.',
            'review[authorEmail]' => 'test@example.com',
        ]);

        self::assertResponseRedirects('/review');
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Köszönjük a véleményed!');
    }

    public function testSubmitWithInvalidEmailShowsForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/new');

        $client->submitForm('Vélemény beküldése', [
            'review[companyName]' => 'Teszt Cég',
            'review[rating]' => '3',
            'review[reviewText]' => 'Valami szöveg ami elég hosszú a validációhoz.',
            'review[authorEmail]' => 'not-a-valid-email',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('.is-invalid');
    }

    public function testSubmitWithEmptyFieldsShowsErrors(): void
    {
        $client = static::createClient();
        $client->request('GET', '/review/new');

        $client->submitForm('Vélemény beküldése', [
            'review[companyName]' => '',
            'review[rating]' => '',
            'review[reviewText]' => '',
            'review[authorEmail]' => '',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorExists('.is-invalid');
    }

    // ── Company stats: average and ordering ──────────────────────

    public function testCompanyStatsAverageAndOrdering(): void
    {
        static::createClient();
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $em->getConnection()->executeStatement('DELETE FROM review');
        $em->getConnection()->executeStatement('DELETE FROM company');

        // Company A: ratings 2 + 4 → avg 3.0
        $companyA = new Company('Alpha Kft', 'alpha kft');
        $em->persist($companyA);
        $em->persist(new Review($companyA, 2, 'Közepes élmény volt összességében.', 'a@test.com'));
        $em->persist(new Review($companyA, 4, 'Jobb mint vártam, örültem neki.', 'b@test.com'));

        // Company B: rating 5 + 5 → avg 5.0
        $companyB = new Company('Beta Zrt', 'beta zrt');
        $em->persist($companyB);
        $em->persist(new Review($companyB, 5, 'Tökéletes, mindent elmondtak előre.', 'c@test.com'));
        $em->persist(new Review($companyB, 5, 'Fantasztikus élmény, visszamegyek.', 'd@test.com'));

        // Company C: rating 1 → avg 1.0
        $companyC = new Company('Gamma Bt', 'gamma bt');
        $em->persist($companyC);
        $em->persist(new Review($companyC, 1, 'Nagyon rossz tapasztalat volt ez.', 'e@test.com'));

        $em->flush();

        /** @var CompanyRepository $companyRepo */
        $companyRepo = static::getContainer()->get(CompanyRepository::class);
        $stats = $companyRepo->findStats();

        self::assertCount(3, $stats);

        // Descending by average rating: B (5.0), A (3.0), C (1.0)
        self::assertSame('Beta Zrt', $stats[0]->companyName);
        self::assertEqualsWithDelta(5.0, $stats[0]->averageRating, 0.01);

        self::assertSame('Alpha Kft', $stats[1]->companyName);
        self::assertEqualsWithDelta(3.0, $stats[1]->averageRating, 0.01);

        self::assertSame('Gamma Bt', $stats[2]->companyName);
        self::assertEqualsWithDelta(1.0, $stats[2]->averageRating, 0.01);

        self::assertSame(2, $stats[1]->reviewCount);
    }
}
