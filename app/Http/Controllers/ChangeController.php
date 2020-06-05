<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CurrencyDenomination;


class ChangeController extends Controller
{
		/**
		 * Calculate the change to return in tender
		 *
		 * @param $request
		 * 
		 * @return array
		 */
    public function calculate(Request $request) {

    	$request->validate([
			    'cost' => 'required',
			    'provided' => 'required'
			]);

    	$cost = $request->input('cost');
    	$provided = $request->input('provided');
    	$currency = $request->input('currency') ?? 'USD';
    	$denominations = CurrencyDenomination::find($currency)->denominations;

    	if ($provided < $cost) {
    		return response('Not enough money provided!', 400);
    	}

    	$changeRemaining = ($provided - $cost) * 100;
	    $tenderList = array();

	    foreach ($denominations as $denomination)
	    {
	        if ($changeRemaining > $denomination)
	        {
	             // Find out how many times this denomination fits in the remaining change
	             $denominationCount = floor($changeRemaining / $denomination);
	             
	             // Update the tender list with the above denomination
	             array_push($tenderList, [
	             	'denomination' => $denomination,
	             	'count' => $denominationCount
	             ]);
	             
	             // Update the remaining change
	             $changeRemaining -= $denomination * $denominationCount;
	        }
	    }

	    if (count($tenderList) <= 0) {
	    	return response('No change required!', 400);
	    }

	    return $tenderList;
    }
}
