<!DOCTYPE html>
<html lang="en">
    @include('includes.vendors.head')
	<body>
	    <header>
	        @include('includes.vendors.header')
	    </header>
	    <section>
	        <div class="mainwrapper">
				<aside>
				@include('includes.vendors.sidebar')
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
