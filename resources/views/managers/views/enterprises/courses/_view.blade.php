{{--
    Partial AJAX: busqueda/filtro + tabla + paginacion de estudiantes
    inscritos en un curso de empresa.

    Se incluye normalmente desde view.blade.php (dentro de #ajax-table-root)
    para el render inicial, y el controller devuelve ESTE mismo partial (sin
    layout) cuando la peticion es AJAX ($request->ajax()) al buscar, filtrar
    o paginar. Ver public/managers/js/ajax-table.js.
--}}
<div class="card card-body">
    <div class="row">
        <div class="col-md-12 col-xl-12">
            <form class="position-relative form-search" action="{{ Request::url() }}" method="GET" id="searchForm">
                <div class="row justify-content-between g-2 ">
                    <div class="col-auto flex-grow-1">
                        <div class="tt-search-box">
                            <div class="input-group">
                                <span class="position-absolute top-50 start-0 translate-middle-y ms-2"> <i
                                        data-feather="search"></i></span>
                                <input class="form-control rounded-start w-100" type="text" id="search"
                                    name="search" placeholder="Buscar" @isset($searchKey) value="{{ $searchKey }}"
                                    @endisset>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <select class="form-select select2" name="culminate"
                                data-minimum-results-for-search="Infinity">
                                <option value="">Seleccionar estado</option>
                                <option value="1" @isset($culminate) @if ($culminate==1) selected @endif @endisset>
                                    Culminado</option>
                                <option value="0" @isset($culminate) @if ($culminate==0) selected @endif @endisset>
                                    Pendiente</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary" data-bs-toggle="tooltip"
                            data-bs-placement="top" data-bs-original-title="Buscar">
                            <i class="fa-duotone fa-magnifying-glass"></i>
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('manager.enterprises.courses.insert', [$enterprise->slack, $course->slack]) }}"
                            class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Ingresar">
                            <i class="fa-regular fa-download"></i>
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('manager.enterprises.courses.reasign',[$enterprise->slack, $course->slack]) }}"
                            class="btn btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Reasignar">
                            <i class="fa-solid fa-left-right"></i>
                        </a>
                    </div>

                    <div class="col-auto">

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="card card-body">
    <div class="table-responsive">
        <table class="table search-table align-middle text-nowrap">
            <thead class="header-item">
                <tr>
                    <th>Identificación</th>
                    <th>Cliente</th>
                    <th>Estado</th>
                    <th>Año</th>
                    <th>Porcentaje</th>
                    <th>Fecha inicio</th>
                    <th>Fecha final</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
              @foreach ($users as $key => $user)
                <tr class="search-items">
                    <td>
                        <span class="usr-email-addr" data-email="{{ $user->identification }}">{{
                            ucfirst($user->identification) }}</span>
                    </td>
                    <td>
                        <span class="usr-email-addr" data-email="{{ $user->firstname . ' ' . $user->lastname }}">{{
                            Str::words( Str::upper(Str::lower($user->firstname . ' ' . $user->lastname)), 12, '...')
                            }}</span>
                    </td>
                    <td>
                        <span
                            class="badge {{ $user->culminated == 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }} rounded-3 py-2 fw-semibold fs-2 d-inline-flex align-items-center gap-1">

                            {{ $user->culminated == 1 ? 'Culminado' : 'Pendiente' }}
                        </span>
                    </td>
                    <td>
                        <span class="usr-ph-no" data-phone="{{ date('Y', strtotime($user->enroll_start)) }}">{{
                            date('Y', strtotime($user->enroll_start)) }}</span>
                    </td>
                    <td>
                        <span class="usr-email-addr" data-email="{{ round($user->percent,2) }}x">{{ round($user->percent,2) }}%</span>
                    </td>
                    <td>
                        <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($user->enroll_start)) }}">{{
                            date('Y-m-d', strtotime($user->enroll_start)) }}</span>
                    </td>
                    <td>
                        <span class="usr-ph-no" data-phone="{{ date('Y-m-d', strtotime($user->enroll_culminated)) }}">{{
                            $user->enroll_culminated== null ? 'PENDIENTE' : date('Y-m-d',
                            strtotime($user->enroll_culminated)) }}</span>
                    </td>
                    <td class="text-left">
                        <div class="dropdown dropstart">
                            <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="fas fa-ellipsis fs-5"></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">


                                @if ($user->enroll_culminated != null)
                                <li>
                                        <a class="dropdown-item d-flex align-items-center gap-3"
                                            href="{{ route('manager.certificate.user', $user->inscription_slack) }}">
                                            Certificado
                                        </a>
                                    </li>
                                @endif

                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-3"
                                        href="{{ route('manager.enterprises.courses.progress', $user->inscription_slack) }}">
                                        Reporte
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-3"
                                        href="{{ route('manager.enterprises.postpone.courses', $user->order_slack) }}">
                                        Ampliar
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </td>
                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
    <div class="result-body ">
        <span>Mostrar {{ $users->firstItem() }}-{{ $users->lastItem() }} de {{ $users->total() }} resultados</span>
        <nav>
            {{ $users->appends(request()->input())->links() }}
        </nav>
    </div>
</div>
