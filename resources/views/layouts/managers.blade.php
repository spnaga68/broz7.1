<!DOCTYPE html>
<html lang="en">
    @include('includes.managers.head')
	<body>
	    <header>
	        @include('includes.managers.header')
	    </header>
	    <section>
	        <div class="mainwrapper">
				<aside>
				@include('includes.managers.sidebar')
				</aside>
				<div class="mainpanel">
					<main>
						@yield('content')
					</main>
				</div><!-- mainpanel -->
	        </div><!-- mainwrapper -->
	    </section>
	</body>
</html>
