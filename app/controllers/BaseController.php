<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
        $this->beforeFilter(function()
        {
            Event::fire('clockwork.controller.start');
        });

        $this->afterFilter(function()
        {
            Event::fire('clockwork.controller.end');
        });
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

}
