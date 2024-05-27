<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class branchDetails
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->check()){
            if(auth()->user()->unit_id){
             return $request->merge([
                    'branch' => 'unit',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->unit->title
             ]);
            }

            if(auth()->user()->division_id){
                return $request->merge([
                    'branch' => 'division',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->division->title
                ]);
            }

            if(auth()->user()->department_id){
                return $request->merge([
                    'branch' => 'department',
                    'branch_id' => auth()->user()->id,
                    'branch_name' =>auth()->user()->department->title
                ]);
            }
        }
        return $next($request);
    }
}
