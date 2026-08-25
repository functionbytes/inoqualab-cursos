{{-- Miniatura esquemática de cada diseño. $shot elige la composición. --}}
@switch($shot)
    @case('nav-h')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="blk pv-fill"></div>
        </div>
        @break

    @case('nav-v')
        <div class="pv-shot">
            <div class="bar pv-sidebar"></div>
            <div class="blk pv-fill"></div>
        </div>
        @break

    @case('dash-a')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="pv-row pv-fill">
                <div class="blk pv-fill"></div>
                <div class="pv-col">
                    <div class="blk pv-fill"></div>
                    <div class="blk pv-fill"></div>
                </div>
            </div>
            <div class="pv-row pv-strip">
                <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
                <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
            </div>
        </div>
        @break

    @case('dash-b')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="acc pv-hero"></div>
            <div class="pv-row pv-fill">
                <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
                <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
                <div class="blk pv-fill"></div>
            </div>
        </div>
        @break

    @case('grid')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="pv-row pv-chips">
                <div class="acc"></div><div class="blk"></div><div class="blk"></div>
            </div>
            <div class="pv-row pv-fill">
                <div class="blk pv-fill"></div><div class="blk pv-fill"></div><div class="blk pv-fill"></div>
            </div>
        </div>
        @break

    @case('rows')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="blk pv-lines"></div>
            <div class="blk pv-lines"></div>
            <div class="blk pv-lines"></div>
            <div class="blk pv-lines"></div>
        </div>
        @break

    @case('list-aside')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="pv-row pv-fill">
                <div class="pv-col">
                    <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
                    <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
                </div>
                <div class="acc pv-aside"></div>
            </div>
        </div>
        @break

    @case('split')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="pv-row pv-fill">
                <div class="pv-col">
                    <div class="blk pv-fill"></div><div class="blk pv-fill"></div><div class="blk pv-fill"></div>
                </div>
                <div class="blk pv-panel"></div>
            </div>
        </div>
        @break

    @case('tree-grid')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="pv-row pv-fill">
                <div class="blk pv-aside"></div>
                <div class="pv-col">
                    <div class="pv-row pv-fill">
                        <div class="blk pv-fill"></div><div class="blk pv-fill"></div><div class="blk pv-fill"></div>
                    </div>
                </div>
            </div>
        </div>
        @break

    @case('form')
        <div class="pv-shot is-stacked">
            <div class="bar pv-topbar"></div>
            <div class="pv-row pv-lines">
                <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
            </div>
            <div class="pv-row pv-lines">
                <div class="blk pv-fill"></div><div class="blk pv-fill"></div>
            </div>
            <div class="acc pv-lines"></div>
        </div>
        @break

    @case('tabs')
        <div class="pv-shot is-stacked">
            <div class="acc pv-hero"></div>
            <div class="pv-row pv-chips">
                <div class="bar"></div><div class="blk"></div><div class="blk"></div>
            </div>
            <div class="blk pv-fill"></div>
        </div>
        @break
@endswitch
