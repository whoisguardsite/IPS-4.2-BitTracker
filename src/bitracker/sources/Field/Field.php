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
 * @Updated     16 FEB 2018
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

namespace IPS\bitracker;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * Field Node
 */
class _Field extends \IPS\CustomField
{
	/**
	 * @brief	[ActiveRecord] Multiton Store
	 */
	protected static $multitons;
	
	/**
	 * @brief	[ActiveRecord] Database Table
	 */
	public static $databaseTable = 'bitracker_cfields';
	
	/**
	 * @brief	[ActiveRecord] Database Prefix
	 */
	public static $databasePrefix = 'cf_';
		
	/**
	 * @brief	[Node] Order Database Column
	 */
	public static $databaseColumnOrder = 'position';

	/**
	 * @brief	[CustomField] Column Map
	 */
	public static $databaseColumnMap = array(
		'not_null'	=> 'not_null'
	);
	
	/**
	 * @brief	[Node] Node Title
	 */
	public static $nodeTitle = 'ccfields';
	
	/**
	 * @brief	[CustomField] Title/Description lang prefix
	 */
	protected static $langKey = 'bitracker_field';

	/**
	 * @brief	[Node] Title prefix.  If specified, will look for a language key with "{$key}_title" as the key
	 */
	public static $titleLangPrefix = 'bitracker_field_';
	
	/**
	 * @brief	[CustomField] Content Table
	 */
	public static $contentDatabaseTable = 'bitracker_ccontent';
	
	/**
	 * @brief	[Node] ACP Restrictions
	 */
	protected static $restrictions = array(
		'app'		=> 'bitracker',
		'module'	=> 'bitracker',
		'prefix'	=> 'fields_',
	);

	/**
	 * @brief	[CustomField] Editor Options
	 */
	public static $editorOptions = array( 'app' => 'bitracker', 'key' => 'bitracker' );
	
	/**
	 * @brief	[CustomField] FileStorage Extension for Upload fields
	 */
	public static $uploadStorageExtension = 'bitracker_FileField';

	/**
	 * Get topic format
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function get_topic_format()
	{
		return $this->format;
	}

	/**
	 * [Node] Add/Edit Form
	 *
	 * @param	\IPS\Helpers\Form	$form	The form
	 * @return	void
	 */
	public function form( &$form )
	{
		parent::form( $form );
		
		if ( \IPS\Application::appIsEnabled( 'forums' ) )
		{
			$form->addHeader('category_forums_integration');
			$form->add( new \IPS\Helpers\Form\YesNo( 'cf_topic', $this->topic ) );
			$form->add( new \IPS\Helpers\Form\TextArea( 'pf_format', $this->id ? $this->topic_format : '', FALSE, array( 'placeholder' => "<strong>{title}:</strong> {content}" ) ) );
		}
	}
	
	/**
	 * [Node] Format form values from add/edit form for save
	 *
	 * @param	array	$values	Values from the form
	 * @return	array
	 */
	public function formatFormValues( $values )
	{
		if ( \IPS\Application::appIsEnabled( 'forums' ) AND isset( $values['cf_topic'] ) )
		{
			$values['topic'] = $values['cf_topic'];
			unset( $values['cf_topic'] );
		}

		$values['search_type']			= ( $values['pf_search_type'] === NULL ) ? '' : $values['pf_search_type'];
		$values['allow_attachments']	= $values['pf_allow_attachments'];
		unset( $values['pf_search_type'] );
		unset( $values['pf_allow_attachments'] );

		return parent::formatFormValues( $values );
	}
}