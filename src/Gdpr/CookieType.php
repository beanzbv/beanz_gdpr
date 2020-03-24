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
    protected $headerScripts;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $footerScripts;

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

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function setHeaderScripts($scripts): self
    {
        $this->headerScripts = $scripts;

        return $this;
    }

    public function setFooterScripts($scripts): self
    {
        $this->footerScripts = $scripts;

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

    /** @return string|null */
    public function getName()
    {
        return $this->name;
    }

    /** @return string|null */
    public function getDescription()
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /** @return string|null */
    public function getHeaderScripts()
    {
        return $this->headerScripts;
    }

    /** @return string|null */
    public function getFooterScripts()
    {
        return $this->footerScripts;
    }

    public function setTranslatedName(string $locale, string $name): self
    {
        Config::save('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.name', $name);

        return $this;
    }

    public function setTranslatedDescription(string $locale, string $description): self
    {
        Config::save('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.description', $description);

        return $this;
    }

    /** @return string|null */
    public function getTranslatedName(string $locale)
    {
        return Config::get('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.name');
    }

    /** @return string|null */
    public function getTranslatedDescription(string $locale)
    {
        return Config::get('beanz.gdpr.disclosure.types.' . $this->getHandle() . '.' . $locale . '.description');
    }

    public function save()
    {
        $em = databaseORM::entityManager();
        $em->persist($this);
        $em->flush();
    }

    public function delete()
    {

        $em = databaseORM::entityManager();
        $em->remove($this);
        $em->flush();
    }

    public function activate()
    {
        $this->setIsActive(true);
        $this->save();
    }

    public function archive()
    {
        $this->setIsActive(false);
        $this->save();
    }

    /**
     * @param string|null $name
     * @param string|null $description
     * @param string|null $headerScripts
     * @param string|null $footerScripts
     */
    public static function add(string $handle, $name, $description, $headerScripts, $footerScripts): self
    {
        $entity = new self();

        $entity
            ->setHandle($handle)
            ->setName($name)
            ->setDescription($description)
            ->setIsActive(true)
            ->setHeaderScripts($headerScripts)
            ->setFooterScripts($footerScripts)
            ->save();

        return $entity;
    }

    /**
     * @param string|null $name
     * @param string|null $description
     * @param string|null $headerScripts
     * @param string|null $footerScripts
     */
    public function update(string $handle, $name, $description, $headerScripts, $footerScripts): self
    {
        $this
            ->setHandle($handle)
            ->setName($name)
            ->setDescription($description)
            ->setHeaderScripts($headerScripts)
            ->setFooterScripts($footerScripts)
            ->save();

        return $this;
    }

    /** @return self|null */
    public static function getByID(int $id)
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['id' => $id], []);
    }

    /** @return self|null */
    public static function getByHandle(string $handle)
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['handle' => $handle], []);
    }

    /** @return self|null */
    public static function getByName(string $name)
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findOneBy(['name' => $name], []);
    }

    /** @return array */
    public static function getActive()
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findBy(['isActive' => true], []);
    }

    /** @return array */
    public static function getAll()
    {
        $em = databaseORM::entityManager();

        return $em->getRepository(get_class())->findBy([], []);
    }
}
