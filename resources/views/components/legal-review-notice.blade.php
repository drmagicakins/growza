{{--
    Rendered on every legal page while `growza-marketing.legal.reviewed` is
    false. Its purpose is to stop an unreviewed draft from being mistaken
    for a binding agreement in production. Flip the config flag only after
    a qualified lawyer in your jurisdiction has reviewed and approved the
    text — see LEVEL 32.
--}}
@unless (config('growza-marketing.legal.reviewed', false))
    <x-alert variant="warning" title="Draft — pending legal review" class="mb-10">
        This document is a structural draft prepared during development. It has not been reviewed by a
        qualified legal practitioner and is not yet a binding agreement. It must be reviewed and approved
        before Growza accepts live customers or payments.
    </x-alert>
@endunless
