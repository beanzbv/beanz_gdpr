<?php
namespace Beanz\Gdpr;

use Concrete\Core\Support\Facade\Config;
use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Support\Facade\DatabaseORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="BeanzGdprCookieTypes")
 */
class CookieType
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $handle;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $isActive;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $scripts;

    /** @var string */
    protected static $table = 'BeanzGdprCookieTypes';

    public static function getTable(): string
    {
        return self::$table;
    }

    public function setHandle(string $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function setScripts(?string $scripts): self
    {
        $this->scripts = $scripts;

        return $this;
    }

    public function getID(): int
    {
        return $this->id;
    }

    public function getHandle(): string
    {
        return $this->handle;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getScripts(): ?string
    {
        return $this->scripts;
    }

    public function setTranslatedName(string $locale, string $name): void
    {
        Config::save('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.name', $name);
    }

    public function setTranslatedDescription(string $locale, string $description): void
    {
        Config::save('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.description', $description);
    }

    public function getTranslatedName(string $locale): ?string
    {
        return Config::get('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.name');
    }

    public function getTranslatedDescription(string $locale): ?string
    {
        return Config::get('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.description');
    }

    public function save(): void
    {
        $em = databaseORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete(): void
    {

        $em = databaseORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public function activate(): void
    {
        $this->setIsActive(true);
        $this->save();
    }

    public function archive(): void
    {
        $this->setIsActive(false);
        $this->save();
    }

    public static function add(
        string $handle,
        ?string $name,
        ?string $description,
        ?string $scripts
    ): self {
        $entity = new self();

        $entity
            ->setHandle($handle)
            ->setName($name)
            ->setDescription($description)
            ->setIsActive(true)
            ->setScripts($scripts)
            ->save();

        return $entity;
    }

    public function update(
        string $handle,
        ?string $name,
        ?string $description,
        ?string $scripts
    ): self {
        $this
            ->setHandle($handle)
            ->setName($name)
            ->setDescription($description)
            ->setScripts($scripts)
            ->save();

        return $this;
    }

    public static function getByID(int $id): ?self
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['id' => $id], []);
    }

    public static function getByHandle(string $handle): ?self
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['handle' => $handle], []);
    }

    public static function getByName(string $name): ?self
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['name' => $name], []);
    }

    public static function getActive(): array
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findBy(['isActive' => true], []);
    }

    public static function getAll(): array
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findBy(['isActive' => true], []);
    }
}
