<?php

namespace Arrounded\Traits;

use Illuminate\Support\Facades\Redirect;

trait Redirectable
{

	////////////////////////////////////////////////////////////////////
	///////////////////////////// REDIRECTIONS /////////////////////////
	////////////////////////////////////////////////////////////////////

	/**
	 * Redirect to an action in the current controller.
	 *
	 * @param string $action
	 * @param array  $parameters
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function redirectHere($action, $parameters = [])
	{
		$controller = get_class($this);

		return Redirect::action($controller.'@'.$action, $parameters);
	}

	/**
	 * Create a redirect for a failed validation.
	 *
	 * @param Validator $validation
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function redirectFailedValidation($validation)
	{
		return Redirect::back()->withInput()->withErrors($validation);
	}

	/**
	 * Redirect back or to a saved URL if any.
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function redirectBackWithSession()
	{
		if ($redirect = Session::get('redirect')) {
			Session::forget('redirect');

			return Redirect::to($redirect);
		}

		return Redirect::back();
	}

	/**
	 * Redirect back, with a fallback if no previous page.
	 *
	 * @param string $fallback
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function redirectBackWithFallback($fallback = '/')
	{
		if (!Request::header('referer')) {
			return Redirect::to($fallback);
		}

		return Redirect::back();
	}

}