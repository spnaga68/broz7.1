<!DOCTYPE html>
<html lang="en">
    @include('includes.admin.head')
	<body>
	    <header>
	        @include('includes.admin.header')
	    </header>
	    <section>
	            <div class="mainwrapper">
						<aside>
						@include('includes.admin.sidebar')
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
