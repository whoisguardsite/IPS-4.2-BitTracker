<?php
/**
 * @brief       BitTracker Application Class
 * @author      Gary Cornell for devCU Software Open Source Projects
 * @copyright   (c) <a href='https://www.devcu.com'>devCU Software Development</a>
 * @license     GNU General Public License v3.0
 * @package     Invision Community Suite 4.2x
 * @subpackage	BitTracker
 * @version     1.0.0 Beta 1
 * @source      https://github.com/GaalexxC/IPS-4.2-BitTracker
 * @Issue Trak  https://www.devcu.com/forums/devcu-tracker/ips4bt/
 * @Created     11 FEB 2018
 * @Updated     15 FEB 2018
 *
 *                    GNU General Public License v3.0
 *    This program is free software: you can redistribute it and/or modify       
 *    it under the terms of the GNU General Public License as published by       
 *    the Free Software Foundation, either version 3 of the License, or          
 *    (at your option) any later version.                                        
 *                                                                               
 *    This program is distributed in the hope that it will be useful,            
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of             
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *                                                                               
 *    You should have received a copy of the GNU General Public License
 *    along with this program.  If not, see http://www.gnu.org/licenses/
 */

namespace IPS\bitracker\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * topBitracker Widget
 */
class _topBitracker extends \IPS\Widget\PermissionCache
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'topBitracker';
	
	/**
	 * @brief	App
	 */
	public $app = 'bitracker';
		
	/**
	 * @brief	Plugin
	 */
	public $plugin = '';

	/**
	 * @brief	Cache Expiration
	 * @note	We allow this cache to be valid for 48 hours
	 */
	public $cacheExpiration = 172800;

	/**
	* Init the widget
	*
	* @return	void
	*/
	public function init()
	{
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'widgets.css', 'bitracker', 'front' ) );

		parent::init();
	}
	
	/**
	 * Specify widget configuration
	 *
	 * @param	null|\IPS\Helpers\Form	$form	Form object
	 * @return	\IPS\Helpers\Form
	 */
	public function configuration( &$form=null )
 	{
 		if ( $form === null )
 		{
	 		$form = new \IPS\Helpers\Form;
 		} 
 		
		$form->add( new \IPS\Helpers\Form\Number( 'number_to_show', isset( $this->configuration['number_to_show'] ) ? $this->configuration['number_to_show'] : 5, TRUE ) );
		return $form;
 	}

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		$categories = array();

		foreach( \IPS\Db::i()->select( 'perm_type_id', 'core_permission_index', array( 'app=? and perm_type=? and (' . \IPS\Db::i()->findInSet( 'perm_' . \IPS\bitracker\Category::$permissionMap['read'], \IPS\Member::loggedIn()->groups ) . ' OR ' . 'perm_' . \IPS\bitracker\Category::$permissionMap['read'] . '=? )', 'bitracker', 'category', '*' ) ) as $category )
		{
			$categories[]	= $category;
		}

		if( !count( $categories ) )
		{
			return '';
		}

		foreach ( array( 'week' => 'P1W', 'month' => 'P1M', 'year' => 'P1Y', 'all' => NULL ) as $time => $interval )
		{			
			$where = array( array( 'file_cat IN(' . implode( ',', $categories ) . ')' ) );
			if ( $interval )
			{
				$where[] = array( 'dtime>?', \IPS\DateTime::create()->sub( new \DateInterval( $interval ) )->getTimestamp() );
			}
			
			$ids	= array();
			$cases	= array();

			foreach( \IPS\Db::i()->select( 'dfid, COUNT(*) AS bitracker', 'bitracker_downloads', $where, 'bitracker DESC', isset( $this->configuration['number_to_show'] ) ? $this->configuration['number_to_show'] : 5, array( 'dfid' ) )->join( 'bitracker_files', 'dfid=file_id' ) as $bitrack )
			{
				$ids[]		= $bitrack['dfid'];
				$cases[]	= "WHEN file_id={$bitrack['dfid']} THEN {$bitrack['bitracker']}";
			}

			if( count( $ids ) )
			{
				$$time = new \IPS\Patterns\ActiveRecordIterator(
					\IPS\Db::i()->select(
						'*, CASE ' . implode( ' ', $cases ) . ' END AS file_bitracker',
						'bitracker_files',
						'file_id IN(' . implode( ',', $ids ) . ')',
						'file_bitracker DESC'
					),
					'IPS\bitracker\File'
				);
			}
			else
			{
				$$time = array();
			}
		}
		
		return $this->output( $week, $month, $year, $all );
	}
}