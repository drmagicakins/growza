<?php

namespace App\Http\Controllers\Web;

use App\Domain\Support\Actions\StoreContactMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('marketing.contact');
    }

    public function store(ContactFormRequest $request, StoreContactMessage $action): RedirectResponse
    {
        $action->handle(
            $request->safe()->except('website'),
            $request->ip(),
            (string) $request->userAgent(),
        );

        return redirect()
            ->route('contact')
            ->with('status', 'Thanks — your message is with us. You will get a reply within one business day.');
    }
}
