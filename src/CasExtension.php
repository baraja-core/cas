<?php

declare(strict_types=1);

namespace Baraja\CAS;


use Baraja\CAS\Service\PasswordAuthorizator;
use Baraja\CAS\Service\UserMetaManager;
use Baraja\Doctrine\ORM\DI\OrmAnnotationsExtension;
use Nette\DI\CompilerExtension;

final class CasExtension extends CompilerExtension
{
	/**
	 * @return array<int, string>
	 */
	public static function mustBeDefinedBefore(): array
	{
		return [
			'Baraja\Doctrine\ORM\DI\OrmAnnotationsExtension',
			'Baraja\Cms\CmsExtension',
		];
	}


	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		if (class_exists(OrmAnnotationsExtension::class)) {
			OrmAnnotationsExtension::addAnnotationPathToManager($builder, 'Baraja\CAS\Entity', __DIR__ . '/Entity');
		}

		$builder->addDefinition($this->prefix('user'))
			->setFactory(User::class);

		$builder->addDefinition($this->prefix('userStorage'))
			->setFactory(UserStorage::class);

		$builder->addDefinition($this->prefix('authenticator'))
			->setFactory(Authenticator::class);

		$builder->addDefinition($this->prefix('userMetaManager'))
			->setFactory(UserMetaManager::class);

		$builder->addDefinition($this->prefix('passwordAuthorizator'))
			->setFactory(PasswordAuthorizator::class);
	}
}
