jQuery(document).ready(function() {	
	 
	var tree = $('#categorytree').checkboxTree({
		onCheck: {
			node: 'expand', 
		},
		onUncheck: {
			node: 'collapse'
		},
		collapseAllElement: '#collapseAll',
		expandAllElement: $('#expandAll'),
	});
	tree.checkboxTree('collapseAll',true);
	
	var btree = $('#brandtree').checkboxTree({
		onCheck: {
			node: 'expand',
			ancestors: 'uncheck'
		},
		onUncheck: {
			node: 'collapse'
		},
		collapseAllElement: '#bcollapseAll',
		expandAllElement: $('#bexpandAll'),
	});
	btree.checkboxTree('collapseAll',true);
	
	 
	 $('#associated-categories-tree-categories-search').autocompletesingle({
            valueKey:'category_name',
            titleKey:'category_name',
            source:[{
                url:sitedata.API+"product_category?fetch=1&term=%QUERY%&limit=10&suppressResponseCodes=true",
                type:'remote',
                getTitle:function(item){
					return item['category_name']
				},
				getValue:function(item){
					return item['category_name']
				},	
                ajax:{
                    dataType : 'jsonp'	
                }
        }]}).on('selected.xdsoft',function(e,datum){
			$('#categorytree').checkboxTree('check', $('#parent_category_'+datum.category_id));
			//$("#categorytree").find('input#category_'+datum.category_id).attr('checked',true);
			$("#category_tree_container").scrollTo(document.getElementById('category_'+datum.category_id), 800);
		});
	 
	 $('#associated-brand-tree-brand-search').autocompletesingle({
            valueKey:'title',
            titleKey:'title',
            source:[{
                url:sitedata.API+"product_brand?fetch=1&term=%QUERY%&limit=10&suppressResponseCodes=true",
                type:'remote',
                getTitle:function(item){
					return item['title']
				},
				getValue:function(item){
					return item['title']
				},	
                ajax:{
                    dataType : 'jsonp'	
                }
        }]}).on('selected.xdsoft',function(e,datum){
			$('#brandtree').checkboxTree('check', $('#parent_brand_'+datum.brand_id));
			//$("#brandtree").find('input#brands_'+datum.brand_id).attr('checked',true);
			$("#brand_tree_container").scrollTo(document.getElementById('brands_'+datum.brand_id), 800);
		});
	 
});

