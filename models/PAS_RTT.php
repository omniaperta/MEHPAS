<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 * This is the model class for table "SILVER.OUT060_RTT".
 *
 * The followings are the available columns in table 'SILVER.OUT060_RTT':
 * @property string $REF_NO
 * @property string $SEQ
 * @property string $X_CN
 * @property string $CLST_DT
 * @property string $CLED_DT
 * @property string $BR_DT
 * @property string $REAS
 * @property string $STATUS
 * @property string $CMNTS
 * @property string $HDDR_GROUP
 * @property string $RTT_ID
 * @property string $CS_STAT
 * @property string $P_START
 * @property string $P_END
 * @property string $P_REAS
 * @property string $P_TEXT
 * @property string $RTT_ORG
 */
class PAS_RTT extends PasAssignedEntity
{
	private static $ACTIVE_STATUS_CODES = array('O');

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'SILVER.OUT060_RTT';
	}

	/**
	 * @return array primary key for the table
	 */
	public function primaryKey()
	{
		return array('REF_NO','SEQ');
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('REF_NO, SEQ, CLST_DT, BR_DT, STATUS', 'safe')
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		return new CActiveDataProvider(get_class($this), array(
				'criteria'=>$criteria,
		));
	}

	/**
	 * Wrapper function for searching for the referral from the PasAssignment object.
	 * NOTE for RTT we expect an arrray of column names and values for this id
	 */
	public function findByExternalId($id)
	{
		if (!is_array($id)) {
			$keys = self::primaryKey();
			foreach (explode(':', $id) as $i => $v) {
				$aid[$keys[$i]] = $v;
			}
			$id = $aid;
		}
		return $this->findByPk($id);
	}

	public function isActive()
	{
		return in_array($this->STATUS, self::$ACTIVE_STATUS_CODES);
	}
}
