<?php

namespace PUGX\MultiUserBundle\Tests\Doctrine;

use PUGX\MultiUserBundle\Doctrine\UserManager;

class UserManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->passwordUpdater = $this->getMockBuilder('FOS\UserBundle\Util\PasswordUpdaterInterface')->getMock();
        $this->fieldsUpdater = $this->getMockBuilder('FOS\UserBundle\Util\CanonicalFieldsUpdater')
            ->disableOriginalConstructor()->getMock();

        $this->om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
                ->disableOriginalConstructor()->getMock();
        $this->userDiscriminator = $this->getMockBuilder('PUGX\MultiUserBundle\Model\UserDiscriminator')
                ->disableOriginalConstructor()->getMock();
        $this->class = 'PUGX\MultiUserBundle\Tests\Stub\User';

        $this->repo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
                ->disableOriginalConstructor()->getMock();

        //parent
        $this->metaData = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')
                ->disableOriginalConstructor()->getMock();

        $this->userManager = new UserManager($this->passwordUpdater, $this->fieldsUpdater, $this->om, $this->class, $this->userDiscriminator);
    }

    public function testGetClass()
    {
        $this->userDiscriminator->expects($this->once())->method('getClass')->will($this->returnValue('Acme\UserBundle\MyUser'));
        $result = $this->userManager->getClass();
        $this->assertEquals('Acme\UserBundle\MyUser', $result);
    }

    public function testCreateUser()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('createUser')->will($this->onConsecutiveCalls(null));
        $this->userManager->createUser();
    }

    public function testFindUserBy()
    {
        $this->userDiscriminator
            ->expects($this->exactly(1))
            ->method('getClasses')
            ->will($this->onConsecutiveCalls(['PUGX\MultiUserBundle\Tests\Stub\User']));

        $this->om->expects($this->exactly(1))
            ->method('getRepository')
            ->will($this->returnValue($this->repo));

        $this->repo->expects($this->exactly(1))
            ->method('findOneBy')
            ->with(['criteria' => 'dummy'])->will($this->onConsecutiveCalls(true));

        $this->userDiscriminator
            ->expects($this->exactly(1))
            ->method('setClass')
            ->will($this->onConsecutiveCalls(['PUGX\MultiUserBundle\Tests\Stub\User']));

        $this->userManager->findUserBy(['criteria' => 'dummy']);
    }

    public function testFindUsers()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('getClasses')->will($this->onConsecutiveCalls(['PUGX\MultiUserBundle\Tests\Stub\User']));
        $this->om->expects($this->exactly(1))->method('getRepository')->with('PUGX\MultiUserBundle\Tests\Stub\User')->will($this->returnValue($this->repo));
        $this->repo->expects($this->exactly(1))->method('findAll')->will($this->onConsecutiveCalls([]));
        $this->userManager->findUsers();
    }

    public function testFindUserByUserNotFound()
    {
        $this->userDiscriminator->expects($this->exactly(1))->method('getClasses')->will($this->onConsecutiveCalls(['PUGX\MultiUserBundle\Tests\Stub\User']));
        $this->om->expects($this->exactly(1))->method('getRepository')->will($this->returnValue($this->repo));
        $this->repo->expects($this->exactly(1))->method('findOneBy')->with(['criteria' => 'dummy'])->will($this->onConsecutiveCalls(null));
        $this->userDiscriminator->expects($this->exactly(0))->method('setClass');
        $user = $this->userManager->findUserBy(['criteria' => 'dummy']);
        $this->assertEquals(null, $user);
    }
}
