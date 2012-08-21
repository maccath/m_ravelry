<?php

class M_Ravelry extends Plugin
{
	/**
	 * Make sure the options are set up
	 **/
	public function action_plugin_activation( $file )
	{
		if(Plugins::id_from_file($file) == Plugins::id_from_file(__FILE__)) {
			if ( Options::get( 'm_ravelry__api_key' ) == null ) {
				Options::set( 'm_ravelry__api_key', '' );
			}
			if ( Options::get( 'm_ravelry__username' ) == null ) {
				Options::set( 'm_ravelry__username', '' );
			}
			if ( Options::get( 'm_ravelry__limit' ) == null ) {
				Options::set( 'm_ravelry__limit', '3' );
			}
			if ( Options::get( 'm_ravelry__in_progress' ) == null ) {
				Options::set( 'm_ravelry__in_progress', true );
			}
			if ( Options::get( 'm_ravelry__hibernating' ) == null ) {
				Options::set( 'm_ravelry__hibernating', false );
			}
			if ( Options::get( 'm_ravelry__frogged' ) == null ) {
				Options::set( 'm_ravelry__frogged', false );
			}
			if ( Options::get( 'm_ravelry__finished' ) == null ) {
				Options::set( 'm_ravelry__finished', false );
			}
		}
	}
	
	public function action_init()
	{
		$this->add_template( 'm_ravelry', dirname(__FILE__) . '/m_ravelry.php' );
		$this->add_template( "block.m_ravelry", dirname( __FILE__ ) . "/block.m_ravelry.php" );
	}
	
	/**
	 * Add new block for themes to use
	 **/
	public function filter_block_list( $block_list )
	{
		$block_list[ 'm_ravelry' ] = _t( "Macca's Ravelry" );
		return $block_list;
	}
	
	/**
	 * Add actions to the plugin page for this plugin
	 **/
	public function filter_plugin_config( $actions, $plugin_id )
	{
		if ( $plugin_id == $this->plugin_id() ) {
			$actions[] = 'Configure';
		}

		return $actions;
	}
	
	/**
	 * Displays the plugin's UI.
	 **/
	public function action_plugin_ui( $plugin_id, $action )
	{
		// Display the UI for this plugin?
		if ( $plugin_id == $this->plugin_id() ) {
			// Depending on the action specified, do different things
			switch ( $action ) {
				case _t( 'Configure' ):
					$ui = new FormUI( get_class( $this ) );
					$ui->append( 'text', 'api_key', 'option:' . 'm_ravelry__api_key', _t( 'Your Ravelry API key' ) );
					$ui->append( 'static', 'api_key_help', _t( "<small>If you don't already have an API key, you can get one at <a href='http://www.ravelry.com/help/api'>http://www.ravelry.com/help/api</a></small>" ) );
					$ui->append( 'text', 'username', 'option:' . 'm_ravelry__username', _t( 'Your Ravelry username' ) );
					$ui->append( 'text', 'limit', 'option:' . 'm_ravelry__limit', _t( 'Number of projects to show' ) );
					$ui->append( 'static', 'limit_help', _t( "<small>Leave empty or set to 0 to show all projects.</small>" ) );
					$status_fieldset = $ui->append( 'fieldset', 'project_statuses', _t( 'Statuses' ) );
					$status_fieldset->append( 'checkbox', 'in_progress', 'option:' . 'm_ravelry__in_progress', _t('In Progress'));
					$status_fieldset->append( 'checkbox', 'hibernating', 'option:' . 'm_ravelry__hibernating', _t('Hibernating'));
					$status_fieldset->append( 'checkbox', 'finished', 'option:' . 'm_ravelry__finished', _t('Finished'));
					$status_fieldset->append( 'checkbox', 'frogged', 'option:' . 'm_ravelry__frogged', _t('Frogged'));
					
					$ui->append( 'submit', 'save', _t( 'Save' ) );
					$ui->set_option( 'success_message', _t( 'Configuration saved' ) );

					$ui->on_success( array( $this, 'updated_config' ) );
					$ui->out();
					break;
			}
		}
	}
	
	/**
	 * When configuration is saved
	 **/
	public function updated_config( $ui )
	{
		Session::notice( _t( 'Ravelry options saved.' ) );
		$ui->save();
		return false;
	}
	
	/**
	 * Add the ravelry CSS to the site
	 **/
	public function action_template_header()
	{
		Stack::add( 'template_stylesheet', array( $this->get_url( true ) . 'm_ravelry.css', 'screen' ), 'm_ravelry' );
	}
	
	/**
	 * Display the default ravelry block
	 **/
	public function theme_m_ravelry( $theme, $block = false )
	{
		if ( ! $block ) {
			$theme->ravelry = $this->build_bars();
		}
		$theme->m_ravelry_placeholder = $this->get_url() . '/images/placeholder.jpg';
		return $theme->fetch( 'm_ravelry' );
	}
	
	/**
	 * Allow configuration of specific Ravelry blocks
	 **/
	public function action_block_form_m_ravelry( $form, $block )
	{
		$form->append( 'text', 'limit', $block, _t( 'Number of projects to show' ) );
		$form->append( 'static', 'limit_help', _t( "<small>Leave empty or set to 0 to show all projects.</small>" ) );
		$status_fieldset = $form->append( 'fieldset', 'project_statuses', _t( 'Statuses' ) );
		$status_fieldset->append( 'checkbox', 'in_progress', $block, _t('In Progress'));
		$status_fieldset->append( 'checkbox', 'hibernating', $block, _t('Hibernating'));
		$status_fieldset->append( 'checkbox', 'finished', $block, _t('Finished'));
		$status_fieldset->append( 'checkbox', 'frogged', $block, _t('Frogged'));
	}

	/**
	 * Handle Ravelry block output
	 **/
	public function action_block_content_m_ravelry( $block, $theme )
	{
		$statuses = array();
		if ( (bool)  $block->in_progress ) $statuses[] = "in-progress";
		if ( (bool)  $block->hibernating ) $statuses[] = "hibernating";
		if ( (bool)  $block->finished ) $statuses[] = "finished";
		if ( (bool)  $block->frogged ) $statuses[] = "frogged";
		
		$theme->ravelry = $this->build_bars( $statuses, $block->limit );
		$theme->block = $block;
	}

	/**
	 * Ravelry Block
	 **/
	function filter_block_content_type_m_ravelry( $types, $block )
	{
		array_unshift( $types, $newtype = "block.{$block->style}.{$block->type}" );
		if ( isset( $block->title ) ) {
			array_unshift( $types, "block.{$block->style}.{$block->type}." . Utils::slugify( $block->title ) );
		}
		return $types;
	}
	
	/**
	 * The main function!
	 **/
	private function build_bars( $statuses = array(), $limit = null )
	{
		if ( empty( $statuses ) ) {
			// Use default configuration options
			if ( (bool)  Options::get( 'm_ravelry__in_progress' ) ) $statuses[] = "in-progress";
			if ( (bool)  Options::get( 'm_ravelry__hibernating' ) ) $statuses[] = "hibernating";
			if ( (bool)  Options::get( 'm_ravelry__finished' ) ) $statuses[] = "finished";
			if ( (bool)  Options::get( 'm_ravelry__frogged' ) ) $statuses[] = "frogged";
		}
		
		// If there isn't a username or password
		if ( ! Options::get( 'm_ravelry__username' ) || ! Options::get( 'm_ravelry__api_key' ) ) {
			return null;
		}
		
		$url = "http://api.ravelry.com/projects/" . Options::get( 'm_ravelry__username' ) . "/progress.json?key=" . Options::get( 'm_ravelry__api_key' ) . "&status=" . join('+', $statuses);
		
		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 600 );

		$output = curl_exec( $ch );
		curl_close( $ch );
		
		if ( ! $output ) {
			return null;
		}
		$decoded = json_decode( $output );
		
		if ( ! $decoded ) return null;
		
		if ( $limit > 0 ) {
			$projects = array();
			$i = 0;
			foreach ( $decoded->projects as $project ) {
				if ( $i < $limit ) {
					$projects[] = $project;
				}
			}
		} else {
			$projects = $decoded->projects;
		}
		
		return $projects;
	}
}
?>
