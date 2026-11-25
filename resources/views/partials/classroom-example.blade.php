<section id="classroom-example" class="how-it-works" aria-labelledby="classroom-example-title">
    <div class="container px-4 px-lg-5">
        <div class="row gx-5 gy-4 align-items-center">
            <div class="col-lg-6">
                <h2 id="classroom-example-title" class="fw-bold">How to create a classroom sociogram</h2>
                <p>Start with a question about a specific activity. The Classroom Interaction Analysis template asks, “Who would you like to work with in a group?” It includes four questions about working together and choosing project partners.</p>
                <p>Create your class list, assign the template and share the access key. Once students respond, open the analysis and look for choices that go in both directions.</p>
                <a class="btn btn-primary rounded-pill px-4 py-2" href="{{ auth()->check() ? route('dashboard', ['starter' => 'classroom']).'#assign-tests' : route('register', ['starter' => 'classroom']) }}">Use this classroom template</a>
            </div>
            <div class="col-lg-6">
                <figure class="feature-card bg-white mb-0">
                    <h3 class="h5">Reading a fictional class example</h3>
                    <ul class="mb-3">
                        <li>Alex chooses Sam.</li>
                        <li>Sam chooses Alex.</li>
                        <li>Jo chooses Sam.</li>
                    </ul>
                    <figcaption>Alex and Sam chose each other. Jo also chose Sam, so Sam receives two choices. A teacher could use this pattern alongside classroom observations when planning the next group activity.</figcaption>
                </figure>
            </div>
        </div>
    </div>
</section>
