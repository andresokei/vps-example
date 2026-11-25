<section class="card mb-4" aria-labelledby="landing-metrics">
    <div class="card-body">
        <h2 id="landing-metrics" class="h5">{{ __('landing.cohorts') }}</h2>
        <p class="small text-muted">{{ __('landing.scope') }}</p>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr>
                    @foreach(['language', 'visits', 'signups', 'mature', 'activated', 'rate'] as $column)
                        <th scope="col">{{ __('landing.'.$column) }}</th>
                    @endforeach
                </tr></thead>
                <tbody>
                    @foreach(['en' => 'English', 'es' => 'Español'] as $locale => $label)
                        @php($cohort = $landingCohorts->get($locale))
                        <tr>
                            <th scope="row" lang="{{ $locale }}">{{ $label }}</th>
                            <td>{{ $landingVisits->get($locale, 0) }}</td>
                            <td>{{ $cohort?->signups ?? 0 }}</td>
                            <td>{{ $cohort?->mature ?? 0 }}</td>
                            <td>{{ $cohort?->activated ?? 0 }}</td>
                            <td>{{ $cohort?->mature ? number_format(100 * $cohort->activated / $cohort->mature, 1).' %' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="small text-muted mb-0">{{ __('landing.definition') }}</p>
    </div>
</section>
