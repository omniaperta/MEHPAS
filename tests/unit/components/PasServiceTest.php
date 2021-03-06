<?php

/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class PasServiceTest extends CDbTestCase
{
	public $fixtures = array(
		'address' => 'Address',
		'contact' => 'Contact',
		'gp' => 'Gp',
		'patient' => 'Patient',
		'practice' => 'Practice',
		'ref' => 'Referral',
		'referral_type' => 'ReferralType',
		'rtt' => 'RTT',
		'service' => 'Service',
		'subspecialty' => 'Subspecialty',
		'service_subspecialty_assignment' => 'ServiceSubspecialtyAssignment',
		'firm' => 'Firm',

	);

	private $pas_gp, $gp_assignment;
	private $pas_practice, $practice_assignment;
	private $pas_patient, $patient_assignment;
	private $pas_referral_type, $pas_referral, $referral_assignment;
	private $pas_rtts, $rtt_assignments;
	private $assign;
	private $service;

	public function setUp()
	{
		parent::setUp();

		$this->pas_gp = ComponentStubGenerator::generate(
			'PAS_Gp',
			array(
				'OBJ_PROF' => 'GP42',
				'NAT_ID' => '12345',
				'FN1' => 'JOHN',
				'FN2' => 'A.',
				'SN' => 'ZOIDBERG',
				'TITLE' => 'DR',
				'TEL_1' => '01234567890',
				'ADD_NAM' => ' PLANET EXPRESS HEADQUARTERS ',
				'ADD_NUM' => '123',
				'ADD_ST' => '57TH STREET',
				'ADD_DIS' => 'MANHATTAN',
				'ADD_TWN' => 'NEW NEW YORK',
				'ADD_CTY' => 'EARTH',
				'PC' => '12345',
				'PRACTICE_CODE' => '67890',
			)
		);

		$this->gp_assignment = ComponentStubGenerator::generate(
			'PasAssignment',
			array(
				'external_type' => 'PAS_Gp',
				'external_id' => 'GP42',
				'external' => $this->pas_gp,
				'internal' => new Gp,
			)
		);
		$this->gp_assignment->expects($this->any())->method('isStale')->will($this->returnValue(true));
		$this->gp_assignment->expects($this->any())->method('save')->will($this->returnValue(true));

		$this->pas_practice = ComponentStubGenerator::generate(
			'PAS_Practice',
			array(
				'OBJ_LOC' => 'PRAC43',
				'PRACTICE_CODE' => '67890',
				'TEL_1' => '01234567890',
				'ADD_NAM' => ' PLANET EXPRESS HEADQUARTERS ',
				'ADD_NUM' => '123',
				'ADD_ST' => '57TH STREET',
				'ADD_DIS' => 'MANHATTAN',
				'ADD_TWN' => 'NEW NEW YORK',
				'ADD_CTY' => 'EARTH',
				'PC' => '12345',
			)
		);

		$this->practice_assignment = ComponentStubGenerator::generate(
			'PasAssignment',
			array(
				'external_type' => 'PAS_Practice',
				'external_id' => 'PRAC43',
				'external' => $this->pas_practice,
				'internal' => new Practice,
			)
		);
		$this->practice_assignment->expects($this->any())->method('isStale')->will($this->returnValue(true));
		$this->practice_assignment->expects($this->any())->method('save')->will($this->returnValue(true));

		$addresses = array(
			ComponentStubGenerator::generate(
				'PAS_PatientAddress',
				array(
					'PROPERTY_NUMBER' => '00100100',
					'PROPERTY_NAME' => 'ROBOT ARMS APTS',
					'ADDR1' => 'MANHATTAN',
					'ADDR2' => 'NEW NEW YORK',
					'ADDR3' => 'UNITED STATES',
					'POSTCODE' => '56789',
					'TEL_NO' => '01234567890',
					'ADDR_TYPE' => 'H',
				)
			),
			ComponentStubGenerator::generate(
				'PAS_PatientAddress',
				array(
					'PROPERTY_NAME' => ' PLANET EXPRESS HEADQUARTERS ',
					'PROPERTY_NUMBER' => ' 123 ',
					'ADDR1' => '57TH STREET',
					'ADDR2' => 'MANHATTAN',
					'ADDR3' => 'NEW NEW YORK',
					'ADDR4' => 'UNITED STATES',
					'POSTCODE' => '12345',
					'ADDR_TYPE' => 'C',
				)
			),
		);

		$this->pas_referral_type = ComponentStubGenerator::generate(
			'PAS_ReferralType',
			array(
				'ULNKEY' => 'SREF',
				'CODE' => $this->referral_type('reftype1')->code
			)
		);

		$this->pas_rtts = array(
			ComponentStubGenerator::generate(
				'PAS_RTT',
				array(
					'CLST_DT' => '2012-05-01',
					'CLED_DT' => '2012-08-25',
					'BR_DT' => '2012-09-02',
					'CMNTS' => 'meh',
				)),
			ComponentStubGenerator::generate(
				'PAS_RTT',
				array(
					'CLST_DT' => '2012-07-21',
					'BR_DT' => '2012-11-22',
					'CMNTS' => 'bah',
				)),
		);

		$this->pas_rtts[0]->expects($this->any())->method('isActive')->will($this->returnValue(false));
		$this->pas_rtts[1]->expects($this->any())->method('isActive')->will($this->returnValue(true));

		$this->rtt_assignments = array(
				ComponentStubGenerator::generate(
						'PasAssignment',
						array(
								'external_type' => 'PAS_RTT',
								'external_id' => '123:1',
								'external' => $this->pas_rtts[0],
								'internal' => new RTT,
						)
				),
				ComponentStubGenerator::generate(
						'PasAssignment',
						array(
								'external_type' => 'PAS_RTT',
								'external_id' => '123:2',
								'external' => $this->pas_rtts[1],
								'internal' => new RTT,
						)
				)
		);

		foreach ($this->rtt_assignments as $assignment) {
			$assignment->expects($this->any())->method('isStale')->will($this->returnValue(true));
			$assignment->expects($this->any())->method('save')->will($this->returnValue(true));
		}

		$this->pas_rtts[0]->assignment = $this->rtt_assignments[0];
		$this->pas_rtts[1]->assignment = $this->rtt_assignments[1];

		$this->pas_referral = ComponentStubGenerator::generate(
				'PAS_Referral',
				array(
						'REFNO' => 123,
						'SRCE_REF' => $this->referral_type('reftype1')->code,
						'DT_REC' => '2012-04-14',
						'pas_ref_type' => $this->pas_referral_type,
						'pas_rtts' => $this->pas_rtts
				)
		);

		$this->referral_assignment = ComponentStubGenerator::generate(
				'PasAssignment',
				array(
						'external_type' => 'PAS_Referral',
						'external_id' => 123,
						'external' => $this->pas_referral,
						'internal' => new Referral,
				)
		);
		$this->referral_assignment->expects($this->any())->method('isStale')->will($this->returnValue(true));
		$this->referral_assignment->expects($this->any())->method('save')->will($this->returnValue(true));

		$this->pas_referral->assignment = $this->referral_assignment;

		$this->pas_patient = ComponentStubGenerator::generate(
			'PAS_Patient',
			array(
				'RM_PATIENT_NO' => 54374,
				'SEX' => 'M',
				'DATE_OF_BIRTH' => '1974-08-09',
				'DATE_OF_DEATH' => null,
				'ETHNIC_GRP' => 'C',
				'hos_number' => ComponentStubGenerator::generate('PAS_PatientNumber', array('NUM_ID_TYPE' => '0', 'NUMBER_ID' => '12345')),
				'nhs_number' => ComponentStubGenerator::generate('PAS_PatientNumber', array('NUM_ID_TYPE' => 'NHS', 'NUMBER_ID' => '123456789')),
				'name' => ComponentStubGenerator::generate('PAS_PatientSurname', array('SURNAME_ID' => 'FRY', 'NAME1' => 'PHILIP', 'TITLE' => 'MR')),
				'address' => $addresses[0],
				'addresses' => $addresses,
				'PatientGp' => ComponentStubGenerator::generate('PAS_PatientGps', array('GP_ID' => 'GP42', 'PRACTICE_CODE' => 'PRAC43')),
				'PatientReferrals' => array($this->pas_referral)
			)
		);

		$patient_assignment = ComponentStubGenerator::generate(
			'PasAssignment',
			array(
				'external_type' => 'PAS_Patient',
				'external_id' => 54374,
				'external' => $this->pas_patient,
				'internal' => new Patient,
			)
		);
		$patient_assignment->expects($this->any())->method('isStale')->will($this->returnValue(true));
		$patient_assignment->expects($this->any())->method('getExternal')->will($this->returnCallback(function () use ($patient_assignment) { return $patient_assignment->external; }));
		$patient_assignment->expects($this->any())->method('save')->will($this->returnValue(true));
		$this->patient_assignment = $patient_assignment;

		$this->assign = $this->getMockBuilder('PasAssignment')->disableOriginalConstructor()->getMock();
		$this->assign->expects($this->any())->method('findByExternal')->will(
			$this->returnValueMap(
				array(
					array('PAS_Gp', 'GP42', $this->gp_assignment),
					array('PAS_Practice', 'PRAC43', $this->practice_assignment),
					array('PAS_Patient', '54374', $this->patient_assignment),
					array('PAS_Referral', 123, $this->referral_assignment),
					array('PAS_RTT', '123:1', $this->rtt_assignments[0]),
					array('PAS_RTT', '123:2', $this->rtt_assignments[1]),
				)
			)
		);

		$this->service = new PasService($this->assign);
		$this->service->setAvailable();

		Yii::app()->params['mehpas_importreferrals'] = false;
		Yii::app()->params['mehpas_legacy_letters'] = false;
	}

	public function testUpdateGpFromPas_New()
	{
		$gp = new Gp;
		$this->assertSame($gp, $this->service->updateGpFromPas($gp, $this->gp_assignment));

		$gp = $this->fetchGp();
		$this->assertEquals('GP42', $gp->obj_prof);
		$this->assertEquals('12345', $gp->nat_id);
		$this->assertEquals('John A.', $gp->contact->first_name);
		$this->assertEquals('Zoidberg', $gp->contact->last_name);
		$this->assertEquals('Dr', $gp->contact->title);
		$this->assertEquals('01234567890', $gp->contact->primary_phone);
		$this->assertEquals("Planet Express Headquarters\n123 57th Street", $gp->contact->address->address1);
		$this->assertEquals("Manhattan", $gp->contact->address->address2);
		$this->assertEquals("New New York", $gp->contact->address->city);
		$this->assertEquals("Earth", $gp->contact->address->county);
		$this->assertEquals("12345", $gp->contact->address->postcode);
	}

	public function testUpdateGpFromPas_Existing()
	{
		$gp = $this->createGp();
		$this->pas_gp->TITLE = 'VISCOUNT';
		$this->assertSame($gp, $this->service->updateGpFromPas($gp, $this->gp_assignment));

		$gp = $this->fetchGp();
		$this->assertEquals('Viscount', $gp->contact->title);
	}

	public function testUpdateGpFromPas_Existing_AddressGone()
	{
		$gp = $this->createGp();
		$this->pas_gp->ADD_NAM = $this->pas_gp->ADD_NUM = $this->pas_gp->ADD_ST = $this->pas_gp->ADD_DIS = $this->pas_gp->ADD_TWN = $this->pas_gp->ADD_CTY = $this->pas_gp->PC = '';
		$this->service->updateGpFromPas($gp, $this->gp_assignment);

		$gp = $this->fetchGp();
		$this->assertNull($gp->contact->address);
	}

	public function testUpdateGpFromPas_Existing_Removed()
	{
		$gp = $this->createGp();
		$this->gp_assignment->external = null;
		$this->gp_assignment->expects($this->once())->method('delete');
		$this->assertNull($this->service->updateGpFromPas($gp, $this->gp_assignment));
	}

	public function testUpdatePracticeFromPas_New()
	{
		$practice = new Practice;
		$this->assertSame($practice, $this->service->updatePracticeFromPas($practice, $this->practice_assignment));

		$practice = $this->fetchPractice();
		$this->assertEquals('PRAC43', $practice->code);
		$this->assertEquals('01234567890', $practice->phone);
		$this->assertEquals('01234567890', $practice->contact->primary_phone);
		$this->assertEquals("Planet Express Headquarters\n123 57th Street", $practice->contact->address->address1);
		$this->assertEquals("Manhattan", $practice->contact->address->address2);
		$this->assertEquals("New New York", $practice->contact->address->city);
		$this->assertEquals("Earth", $practice->contact->address->county);
		$this->assertEquals("12345", $practice->contact->address->postcode);
	}

	public function testUpdatePracticeFromPas_Existing()
	{
		$practice = $this->createPractice();
		$this->pas_practice->TEL_1 = '09876543210';
		$this->assertSame($practice, $this->service->updatePracticeFromPas($practice, $this->practice_assignment));

		$practice = $this->fetchPractice();
		$this->assertEquals('09876543210', $practice->phone);
		$this->assertEquals('09876543210', $practice->contact->primary_phone);
	}

	public function testUpdatePracticeFromPas_Existing_AddressGone()
	{
		$practice = $this->createPractice();
		$this->pas_practice->ADD_NAM = $this->pas_practice->ADD_NUM = $this->pas_practice->ADD_ST = $this->pas_practice->ADD_DIS = $this->pas_practice->ADD_TWN = $this->pas_practice->ADD_CTY = $this->pas_practice->PC = '';
		$this->service->updatePracticeFromPas($practice, $this->practice_assignment);

		$practice = $this->fetchPractice();
		$this->assertNull($practice->contact->address);
	}

	public function testUpdatePracticeFromPas_Existing_Removed()
	{
		$practice = $this->createPractice();
		$this->practice_assignment->external = null;
		$this->practice_assignment->expects($this->once())->method('delete');
		$this->assertNull($this->service->updatePracticeFromPas($practice, $this->practice_assignment));
	}

	public function testUpdatePatientFromPas_New()
	{
		$this->service->updatePatientFromPas(new Patient, $this->patient_assignment);

		$patient = $this->fetchPatient();
		$this->assertEquals('012345', $patient->pas_key);
		$this->assertEquals('012345', $patient->hos_num);
		$this->assertEquals('123456789', $patient->nhs_num);
		$this->assertEquals('1974-08-09', $patient->dob);
		$this->assertNull($patient->date_of_death);
		$this->assertEquals($this->gp_assignment->internal_id, $patient->gp_id);
		$this->assertEquals($this->practice_assignment->internal_id, $patient->practice_id);
		$this->assertEquals(3, $patient->ethnic_group_id);
	}

	public function testUpdatePatientFromPas_New_WithReferral()
	{
		Yii::app()->params['mehpas_importreferrals'] = true;

		$this->service->updatePatientFromPas(new Patient, $this->patient_assignment);

		$referrals = $this->fetchPatient()->referrals;
		$this->assertCount(1, $referrals);
		$this->assertEquals(123, $referrals[0]->refno);
		$this->assertEquals($this->referral_type('reftype1')->id, $referrals[0]->referral_type_id);
		$this->assertEquals('2012-04-14', $referrals[0]->received_date);

		$rtts = $referrals[0]->rtts;
		$this->assertCount(2, $rtts);

		$this->assertEquals('2012-05-01', $rtts[0]->clock_start);
		$this->assertEquals('2012-08-25', $rtts[0]->clock_end);
		$this->assertEquals('2012-09-02', $rtts[0]->breach);
		$this->assertEquals(0, $rtts[0]->active);
		$this->assertEquals('meh', $rtts[0]->comments);

		$this->assertEquals('2012-07-21', $rtts[1]->clock_start);
		$this->assertEquals(null, $rtts[1]->clock_end);
		$this->assertEquals('2012-11-22', $rtts[1]->breach);
		$this->assertEquals(1, $rtts[1]->active);
		$this->assertEquals('bah', $rtts[1]->comments);
	}

	public function testUpdatePatientFromPas_New_WithReferral_Subspecialty()
	{
		Yii::app()->params['mehpas_importreferrals'] = true;

		$this->pas_referral->REF_PERS = "FAKE";
		$this->pas_referral->REF_SPEC = $this->subspecialty('subspecialty1')->ref_spec;

		$this->service->updatePatientFromPas(new Patient, $this->patient_assignment);

		$referrals = $this->fetchPatient()->referrals;
		$this->assertEquals(1, $referrals[0]->service_subspecialty_assignment_id);
		$this->assertNull($referrals[0]->firm_id);
	}

	public function testUpdatePatientFromPas_New_WithReferral_Firm()
	{
		Yii::app()->params['mehpas_importreferrals'] = true;

		$this->pas_referral->REF_PERS = $this->firm('firm1')->pas_code;
		$this->pas_referral->REF_SPEC = $this->subspecialty('subspecialty1')->ref_spec;

		$this->service->updatePatientFromPas(new Patient, $this->patient_assignment);

		$referrals = $this->fetchPatient()->referrals;
		$this->assertEquals(1, $referrals[0]->service_subspecialty_assignment_id);
		$this->assertEquals($this->firm('firm1')->id, $referrals[0]->firm_id);

	}

	public function testUpdatePatientFromPas_Existing_Removed()
	{
		$patient = $this->createPatient();
		$this->patient_assignment->external = null;
		$this->service->updatePatientFromPas($patient, $this->patient_assignment);
		$this->assertEquals(1, $this->patient_assignment->missing_from_pas);
	}

	public function testUpdatePatientFromPas_Existing_GpRemoved()
	{
		$patient = $this->createPatient();
		$this->gp_assignment->external = null;
		$this->service->updatePatientFromPas($patient, $this->patient_assignment);

		$patient = $this->fetchPatient();
		$this->assertNull($patient->gp);
	}

	public function testUpdatePatientFromPas_Existing_PracticeRemoved()
	{
		$patient = $this->createPatient();
		$this->practice_assignment->external = null;
		$this->service->updatePatientFromPas($patient, $this->patient_assignment);

		$patient = $this->fetchPatient();
		$this->assertNull($patient->practice);
	}

	public function testUpdatePatientFromPas_Existing_GpAndPracticeRemoved()
	{
		$patient = $this->createPatient();
		$this->gp_assignment->external = null;
		$this->practice_assignment->external = null;
		$this->service->updatePatientFromPas($patient, $this->patient_assignment);

		$patient = $this->fetchPatient();
		$this->assertNull($patient->gp);
		$this->assertNull($patient->practice);
	}

	public function testCreateOrUpdatePatient_Create()
	{
		$this->pas_patient->expects($this->any())->method('getAllHosNums')->will($this->returnValue(array('012345')));
		$this->patient_assignment->isNewRecord = true;

		$this->service->createOrUpdatePatient('54374');

		$patient = $this->fetchPatient();
		$this->assertEquals('012345', $patient->pas_key);
		$this->assertEquals('012345', $patient->hos_num);
		$this->assertEquals('123456789', $patient->nhs_num);
		$this->assertEquals('1974-08-09', $patient->dob);
		$this->assertNull($patient->date_of_death);
		$this->assertEquals($this->gp_assignment->internal_id, $patient->gp_id);
		$this->assertEquals($this->practice_assignment->internal_id, $patient->practice_id);
		$this->assertEquals(3, $patient->ethnic_group_id);
	}

	public function testCreateOrUpdatePatient_Update()
	{
		$this->createPatient();

		$this->pas_patient->expects($this->any())->method('getAllHosNums')->will($this->returnValue(array('012345')));
		$this->patient_assignment->isNewRecord = false;

		$this->pas_patient->name->SURNAME_ID = 'FOO';

		$this->service->createOrUpdatePatient('54374');

		$patient = $this->fetchPatient();
		$this->assertEquals('Foo', $patient->contact->last_name);
	}

	public function testCreateOrUpdatePatient_Merge()
	{
		$patient = $this->createPatient();

		$old_assignment = ComponentStubGenerator::generate(
			'PasAssignment',
			array(
				'external_type' => 'PAS_Patient',
				'external_id' => '54734',
				'external' => null,
				'internal_type' => 'Patient',
				'internal_id' => $patient->id,
				'internal' => $patient,
			)
		);
		$this->assign->expects($this->any())->method('findByInternal')->with('Patient', $patient->id)->will($this->returnValue($old_assignment));

		$this->pas_patient->expects($this->any())->method('getAllHosNums')->will($this->returnValue(array('056789', '012345')));
		$this->pas_patient->RM_PATIENT_NO = '67894';
		$this->pas_patient->hos_number->NUMBER_ID = '56789';
		$this->patient_assignment->external_id = '67894';
		$this->patient_assignment->internal_id = null;
		$this->patient_assignment->isNewRecord = true;
		$this->patient_assignment->internal = $patient;

		$this->service->createOrUpdatePatient('54374');

		$this->assertEquals($this->patient_assignment->internal_id, $patient->id);
		$patient = $this->fetchPatient();
		$this->assertEquals('056789', $patient->hos_num);
	}

	private function createGp()
	{
		return $this->service->updateGpFromPas(new Gp, $this->gp_assignment);
	}

	private function createPractice()
	{
		return $this->service->updatePracticeFromPas(new Practice, $this->practice_assignment);
	}

	private function createPatient()
	{
		$this->service->updatePatientFromPas(new Patient, $this->patient_assignment);
		return $this->fetchPatient();
	}

	private function fetchGp()
	{
		return Gp::model()->noPas()->findByPk($this->gp_assignment->internal_id);
	}

	private function fetchPractice()
	{
		return Practice::model()->noPas()->findByPk($this->practice_assignment->internal_id);
	}

	private function fetchPatient()
	{
		return Patient::model()->noPas()->findByPk($this->patient_assignment->internal_id);
	}
}
