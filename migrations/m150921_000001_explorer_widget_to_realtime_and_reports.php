<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150921_000001_explorer_widget_to_realtime_and_reports extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$rows = craft()->db->createCommand()
			->select('*')
			->from('widgets')
			->where('type=:type', array(':type'=>'Analytics_Explorer'))
			->queryAll();

		if($rows)
		{
			foreach($rows as $row)
			{
				$oldSettings = JsonHelper::decode($row['settings']);


				// old to new

				$newSettings = [];

				if(isset($oldSettings['chart']))
				{
					$newSettings['chart'] = $oldSettings['chart'];
				}

				if(isset($oldSettings['period']))
				{
					$newSettings['period'] = $oldSettings['period'];
				}

				$newSettings['options'] = [];

				if(isset($oldSettings['dimension']))
				{
					$newSettings['options']['dimension'] = $oldSettings['dimension'];
				}

				if(isset($oldSettings['metric']))
				{
					$newSettings['options']['metric'] = $oldSettings['metric'];
				}

				switch($oldSettings['menu'])
				{
					case 'realtimeVisitors':
						$type='Analytics_Realtime';
						break;

					default:
						$type='Analytics_Report';
				}


				// update row

				$newSettings = JsonHelper::encode($newSettings);

				$updateCmd = craft()->db->createCommand()
					->update('widgets', array('type' => $type, 'settings' => $newSettings), 'id=:id', array('id' => $row['id']));
			}
		}

		return true;
	}
}
