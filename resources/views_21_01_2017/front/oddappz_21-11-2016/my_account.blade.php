@extends('layouts.app')
@section('content')

<section class="store_item_list">
<div class="container">
<div class="row">
<div class="my_account_section">
<div id="parentHorizontalTab">
<div class="col-md-3">
@include('front.'.Session::get("general")->theme.'.profile_sidebar')
                        </div>
						
						<div class="col-md-9">
                            <div class="right_descript">
                                <div class="resp-tabs-container hor_1">
                                    <div class="elections_sections">
                                        <div class="tabs effect-3">
                                            <!-- tab-content -->
                                            <div class="tab-content">
                                                <section id="tab-item-1" class="edit_profile">
<div class="payment_info">
<h2 class="pay_title">Payment info</h2>
<div class="col-md-10">
<h3>55XX XXXX XXXX XX65 CVC : 348  EXP : 12/16</h3>
<button type="button" class="btn btn-primary" title="Add new card">Add new card</button>
</div>
<div class="col-md-2 common_right">
<a href="#" class="delet_icon" title=""><i class="glyph-icon flaticon-delete-1"></i></a>
<a href="#" class="edit_icon" title=""><i class="glyph-icon flaticon-write"></i></a>

</div>
</div>   
<div class="payment_info">
<h2 class="pay_title">Address book</h2>
<div class="col-md-10">
<h3>
<address>
Abu Baker<br/>
3rd block, Gulf street,<br/>
Sharq market,<br/>
Kuwait.
</address>
</h3>
<button type="button" class="btn btn-primary" title="@lang('messages.Add new address')">Add new address</button>
</div>
<div class="col-md-2 common_right">
<a href="#" class="delet_icon" title=""><i class="glyph-icon flaticon-delete-1"></i></a>
<a href="#" class="edit_icon" title=""><i class="glyph-icon flaticon-write"></i></a>

</div>
</div>                                            
											   
												 
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="profile_sections">
                                      <h2 class="pay_title">My orders</h2>
                                        <div class="my_account_sections">
                                           <div class="table-responsive"> <table class="table">
    <thead>
        <tr>
            <th>Order Id </th>
            <th> Store name</th>
            <th>Price</th>
            <th>Status</th>
            <th>Date</th>
            <th>View</th>
            <th>Remove</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>#ORSUL009</td>
            <td>The Sultan center</td>
            <td>5.490KD</td>
            <td><p>Processing</p></td>
            <td>20/5/2016</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-view"></i>
			</a>
			</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-delete"></i>
			</a>
			</td>
        </tr>
        <tr>
            <td>#ORSUL009</td>
            <td>The Sultan center</td>
            <td>5.490KD</td>
            <td>Returned</td>
            <td>20/5/2016</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-view"></i>
			</a>
			</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-delete"></i>
			</a>
			</td>
        </tr>
            <tr>
            <td>#ORSUL009</td>
            <td>The Sultan center</td>
            <td>5.490KD</td>
            <td><span class="color_red">Cancelled</span></td>
            <td>20/5/2016</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-view"></i>
			</a>
			</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-delete"></i>
			</a>
			</td>
        </tr>
		   <tr>
            <td>#ORSUL009</td>
            <td>The Sultan center</td>
            <td>5.490KD</td>
            <td><span class="color_green">Delivered</span></td>
            <td>20/5/2016</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-view"></i>
			</a>
			</td>
            <td valign="middle">
			<a title="" href="#">
			<i class="glyph-icon flaticon-delete"></i>
			</a>
			</td>
        </tr>
    </tbody>
</table>
 </div>
        </div>
        </div>
        <div class="edit_profile_section">
                                        <h2 class="pay_title">My favourites</h2>
                                        <div class="my_fav_sec">
                                            <div class="col-md-4 col-sm-4 col-xs-6">
	<div class="common_item">
	<div class="store_itm_img">
	<a title="" href="#"><img alt="img" src="images/img1.jpg"></a>
	</div>
	<div class="store_itm_desc">
	<a title="The Sultan store" href="#">The Sultan store</a>
	<p>Breakfast, Bakery &amp; more</p>
	</div>
	<div class="store_itm_rating">
	<h2><a title="" href="#"><img alt="img" src="images/rrating.png"></a>  4.5</h2>
	<span class="favi"><i class="glyph-icon flaticon-favorite-heart-button"></i></span>
	</div>
	</div>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-6">
	<div class="common_item">
	<div class="store_itm_img">
	<a title="" href="#"><img alt="img" src="images/img1.jpg"></a>
	<div class="price_sec">
	<b>20</b>
<p>min</p>
	</div>
	</div>
	<div class="store_itm_desc">
	<a title="The Sultan store" href="#">The Sultan store</a>
	<p>Breakfast, Bakery &amp; more</p>
	</div>
	<div class="store_itm_rating">
	<h2><a title="" href="#"><img alt="img" src="images/rrating.png"></a>  4.5</h2>
	<span class="favi"><i class="glyph-icon flaticon-favorite-heart-button"></i></span>
	</div>
	</div>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-6">
	<div class="common_item">
	<div class="store_itm_img">
	<a title="" href="#"><img alt="img" src="images/img1.jpg"></a>
	</div>
	<div class="store_itm_desc">
	<a title="The Sultan store" href="#">The Sultan store</a>
	<p>Breakfast, Bakery &amp; more</p>
	</div>
	<div class="store_itm_rating">
	<h2><a title="" href="#"><img alt="img" src="images/rrating.png"></a>  4.5</h2>
	<span class="favi"><i class="glyph-icon flaticon-favorite-1"></i></span>
	</div>
	</div>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-6">
	<div class="common_item">
	<div class="store_itm_img">
	<a title="" href="#"><img alt="img" src="images/img1.jpg"></a>
	<div class="price_sec">
	<b>20</b>
<p>min</p>
	</div>
	</div>
	<div class="store_itm_desc">
	<a title="The Sultan store" href="#">The Sultan store</a>
	<p>Breakfast, Bakery &amp; more</p>
	</div>
	<div class="store_itm_rating">
	<h2><a title="" href="#"><img alt="img" src="images/rrating.png"></a>  4.5</h2>
	<span class="favi"><i class="glyph-icon flaticon-favorite-1"></i></span>
	</div>
	</div>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-6">
	<div class="common_item">
	<div class="store_itm_img">
	<a title="" href="#"><img alt="img" src="images/img1.jpg"></a>
	</div>
	<div class="store_itm_desc">
	<a title="The Sultan store" href="#">The Sultan store</a>
	<p>Breakfast, Bakery &amp; more</p>
	</div>
	<div class="store_itm_rating">
	<h2><a title="" href="#"><img alt="img" src="images/rrating.png"></a>  4.5</h2>
	<span class="favi"><i class="glyph-icon flaticon-favorite-1"></i></span>
	</div>
	</div>
	</div>
	<div class="col-md-4 col-sm-4 col-xs-6">
	<div class="common_item">
	<div class="store_itm_img">
	<a title="" href="#"><img alt="img" src="images/img1.jpg"></a>
	<div class="price_sec">
	<b>20</b>
<p>min</p>
	</div>
	</div>
	<div class="store_itm_desc">
	<a title="The Sultan store" href="#">The Sultan store</a>
	<p>Breakfast, Bakery &amp; more</p>
	</div>
	<div class="store_itm_rating">
	<h2><a title="" href="#"><img alt="img" src="images/rrating.png"></a>  4.5</h2>
	<span class="favi"><i class="glyph-icon flaticon-favorite-1"></i></span>
	</div>
	</div>
	</div>
											
                                            </div>
                                        </div>
										<div class="edit_profile_section">
                                        <h2 class="pay_title">Change password</h2>
                                        <div class="change_password">
										<div class="election_change_pass">
                                            <form>
                                                <div class="col-md-12">
                                                   <div class="form-group"> <input type="password" placeholder="Old password" id="exampleInputPassword1" class="form-control"> </div>
                                                </div>
                                                <div class="col-md-12">
                                                   <div class="form-group"> <input type="password" placeholder="New password" id="exampleInputPassword1" class="form-control"> </div>
                                                <div class="form-group"> <input type="password" placeholder="Retype password" id="exampleInputPassword1" class="form-control"> </div>
                                                    <div class="col-md-12 padding0">
                                                        <div class="button_sections">
                                                            <button type="button" class="btn btn-primary" title="Cancel">Cancel</button>
                                                            <button type="submit" class="btn btn-default" title="Update">Update</button>
                                                        </div>
                                                    </div>

                                            
                                            </div></form>
                                        </div>
										</div>
										</div>
										
										
										<div class="edit_profile_section">
											<h2 class="pay_title">Edit profile</h2>
											<div class="edit_orofile_sections_inner">
												<form>
													<div class="col-md-6">
														<div class="form-group">
														<input type="text" class="form-control"  required placeholder="First Name">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
														<input type="text" class="form-control"  required placeholder="Last Name">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
														<input type="email" class="form-control" required placeholder="Email">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
														<input type="text" class="form-control"  required placeholder="Phone">
														</div>
													</div>
													<div class="col-md-6">
														<div class="button_sections">
															<button title="@lang('messages.Cancel')" class="btn btn-primary" type="button">@lang('messages.Cancel')</button>
															<button title="@lang('messages.Update')" class="btn btn-default" type="submit">@lang('messages.Update')</button>
														</div>
													</div>
												</form>
											</div>
										</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
							</div>
</div>
</div>
</section>
    <!-- footer section strat end -->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
     <!--Plug-in Initialisation-->
    <script type="text/javascript">
		$('select').select2();
        $(document).ready(function() {
            //Horizontal Tab
            $('#parentHorizontalTab').easyResponsiveTabs({
                type: 'default', //Types: default, vertical, accordion
                width: 'auto', //auto or any width like 600px
                fit: true, // 100% fit in a container
                tabidentify: 'hor_1', // The tab groups identifier
                activate: function(event) { // Callback function if tab is switched
                    var $tab = $(this);
                    var $info = $('#nested-tabInfo');
                    var $name = $('span', $info);
                    $name.text($tab.text());
                    $info.show();
                }
            });
        });
    </script>
	
@endsection