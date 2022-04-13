@extends('web::layouts.app')

@section('title', "Inventory Dashboard")
@section('page_header', "Inventory Dashboard")


@section('content')
    <div id="content-target"></div>
@stop

@push("javascript")
    <script src="@inventoryVersionedAsset('inventory/js/w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/select2w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/bootstrapPopUpW2.js')"></script>


    <script>
        async function jsonPostAction(url,data){
            return await fetch(url, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data),
            })
        }

        class LocationFilterComponent extends W2.W2Component {
            locationListeners
            id

            constructor(options) {
                super();

                this.id = W2.getID("locationFilterSelect",true)

                this.locationListeners = options.locationListeners || []
            }

            locationSelected(selection) {
                let locationID = null
                if (selection) {
                    locationID = selection.id
                }

                for (const locationListener of this.locationListeners) {
                    locationListener(locationID)
                }
            }

            render(container) {
                const card = W2
                    .html("div")
                    .class("card")
                    .content(
                        W2.html("div")
                            .class("card-body")
                            .content(
                                W2.html("label")
                                    .attribute("for", this.id)
                                    .content("Location")
                            )
                            .content(
                                select2Component({
                                    select2: {
                                        placeholder: "All locations",
                                        ajax: {
                                            url: "{{ route("inventory.mainFilterLocationSuggestions") }}"
                                        },
                                        allowClear: true,
                                    },
                                    id: this.id,
                                    selectionListeners: [
                                        (...args) => this.locationSelected(...args)
                                    ]
                                })
                            )
                            .content(
                                W2.html("small")
                                    .class("text-muted")
                                    .content("Only show categories containing stocks at a specific location.")
                            )
                    )

                container.content(card)
            }
        }

        function confirmButtonComponent(text,callback) {
            const state = {
                firstStep: true
            }
            return W2.mount(state,(container, mount, state)=>{
                if(state.firstStep){
                    container.content(
                        W2.html("button")
                            .class("btn btn-danger")
                            .content(text)
                            .event("click",()=>{
                                state.firstStep = false
                                mount.update()
                            })
                    )
                } else {
                    container.content(
                        W2.html("div")
                            .class("btn-group")
                            .content(
                                W2.html("button")
                                    .class("btn btn-primary")
                                    .content("Cancel")
                                    .event("click",()=>{
                                        state.firstStep = true
                                        mount.update()
                                    })
                            )
                            .content(
                                W2.html("button")
                                    .class("btn btn-warning")
                                    .content("Confirm")
                                    .event("click",()=>{
                                        callback()
                                        state.firstStep = true
                                        mount.update()
                                    })
                            )
                    )
                }
            })
        }

        function editCategoryPopUp(app,category) {
            BootstrapPopUp.open(category.id ? "Edit Category" : "Create Category", (container, popup) => {
                const state = {
                    name: category.name || "",
                    message: null,
                    stocks: category.stocks,
                    selectedStock: null
                }

                const mount = W2.mount(state,(container, mount, state)=>{
                    container
                        .content(
                            W2.html("div")
                                .class("form-group")
                                .content(
                                    W2.html("label")
                                        .attribute("for", W2.getID("editCategoryNameLabel", true))
                                        .content("Category Name")
                                )
                                .content(
                                    W2.html("input")
                                        .class("form-control")
                                        .attribute("type", "text")
                                        .attribute("placeholder", "Enter the category name")
                                        .attribute("value", state.name)
                                        .event("change",(e)=>{
                                            state.name = e.target.value
                                        })
                                )
                                .content((container)=>{
                                    if(state.message){
                                        container.content(
                                            W2.html("small").class("text-danger").content(state.message)
                                        )
                                    }
                                })
                        )
                        .content(
                            W2.html("div")
                                .class("form-group")
                                .content(
                                    W2.html("label")
                                        .content("Add Stock")
                                        .attribute("for",W2.getID("editCategoryAddStockLabel"))
                                )
                                .content(
                                    select2Component({
                                        select2: {
                                            placeholder: "Select stock",
                                            ajax: {
                                                url: "{{ route("inventory.addStockSuggestion") }}",
                                                data: function (params) {
                                                    return {
                                                        term: params.term,
                                                    }
                                                },
                                                processResults: (data)=>{
                                                    return {
                                                        results: data.results.filter((data)=>{
                                                            const includedIDs = state.stocks.map((entry)=>entry.id)
                                                            return !includedIDs.includes(data.id.id)
                                                        })
                                                    }
                                                }
                                            },
                                            allowClear: true,
                                        },
                                        id: W2.getID("editCategoryAddStockLabel"),
                                        selectionListeners: [
                                            (data) => {
                                                state.selectedStock = data.id
                                            }
                                        ]
                                    })
                                )
                                .content(
                                    W2.html("button")
                                        .class("btn btn-secondary btn-block mt-2")
                                        .content("Add Stock")
                                        .event("click",()=>{
                                            if(state.selectedStock) {
                                                state.stocks.push(state.selectedStock)
                                                mount.update()
                                            }
                                        })
                                )
                                .content((container)=>{
                                    if(state.stocks.length > 0){
                                        container.content(
                                            W2.html("ul")
                                                .class("list-group list-group-flush")
                                                .content((container)=>{
                                                    for (const stock of state.stocks) {
                                                        container.content(
                                                            W2.html("li")
                                                                .class("list-group-item d-flex align-items-baseline justify-content-between")
                                                                .style("padding-right","0")
                                                                .content(stock.name)
                                                                .content(
                                                                    W2.html("button")
                                                                        .class("float-right btn btn-danger")
                                                                        .content("×")
                                                                        .event("click",()=>{
                                                                            const index = state.stocks.indexOf(stock)
                                                                            state.stocks.splice(index,1)
                                                                            mount.update()
                                                                        })
                                                                )
                                                        )
                                                    }
                                                })
                                        )
                                    } else {
                                        container.content(
                                            W2.html("p")
                                                .class("mt-3")
                                                .content("You haven't added any stock to this category")
                                        )
                                    }
                                })
                        )
                        .content(
                            W2.html("div")
                                .class("d-flex flex-row")
                                .content(
                                    (container)=>{
                                        //delete button
                                        if(category.id) {
                                            container.content(
                                                confirmButtonComponent("Delete", async () => {
                                                    popup.close()

                                                    const response = await jsonPostAction("{{ route("inventory.deleteCategory") }}",{
                                                        id: category.id
                                                    })

                                                    if (!response.ok) {
                                                        BoostrapToast.open("Category", "Failed to delete the category")
                                                    } else {
                                                        BoostrapToast.open("Category", "Successfully deleted the category")
                                                    }

                                                    app.categoryList.state.loadData()
                                                })
                                            )
                                        }
                                    }
                                )
                                .content(
                                    //close button
                                    W2.html("button")
                                        .class("btn btn-secondary mr-1 ml-auto")
                                        .content("Close")
                                        .event("click",()=>popup.close())
                                )
                                .content(
                                    //save button
                                    W2.html("button")
                                        .class("btn btn-primary")
                                        .content("Save")
                                        .event("click",async () => {
                                            if (state.name && state.name.length > 0) {
                                                //name field is not empty, save the category

                                                popup.close()

                                                const data = {
                                                    id: category.id,
                                                    name: state.name,
                                                    stocks: state.stocks.map((e)=>e.id)
                                                }

                                                const response = await jsonPostAction("{{ route("inventory.saveCategory") }}",data)

                                                if(!response.ok){
                                                    BoostrapToast.open("Category","Failed to save the category")
                                                } else {
                                                    BoostrapToast.open("Category","Successfully saved category")
                                                }

                                                app.categoryList.state.loadData()

                                            } else {
                                                //name field is empty
                                                state.message = "Please enter a valid name!"
                                                mount.update()
                                            }
                                        })
                                )
                        )
                })

                container.content(mount)
            })
        }

        function stockCardPropertyEntry(name, value, style=null) {
            let effectiveText = value
            let addToolTip = false
            if(effectiveText.length >= 20){
                effectiveText = effectiveText.substring(0,20)
                addToolTip = true
            }

            return W2.html("li")
                .class("list-group-item")
                .classIf(style,`list-group-item-${style}`)
                .content(name)
                .content(
                    tooltipComponent(
                        W2.html("b")
                            .class("float-right")
                            .content(effectiveText),
                        addToolTip ? value : ""
                    )
                )
        }

        function stockCardComponent(app, stock, category) {
            const available = stock.available_on_contracts + stock.available_in_hangars

            let availabilityColor = null
            if(available === 0){
                availabilityColor = "danger"
            } else if(available < stock.warning_threshold){
                availabilityColor = "warning"
            }

            return W2.html("div")
                .class("card m-1")
                .style("width","16rem")
                .content(
                    //card header
                    W2.html("div")
                        .class("card-header d-flex align-items-baseline")
                        .style("padding-right","0.75rem")
                        .content(
                            W2.html("h5")
                                .class("card-title mr-auto")
                                .content(
                                    W2.html("a")
                                        .attribute("href",`/inventory/stocks/view/${stock.id}`)
                                        .content(stock.name)
                                )
                        )
                        .content(
                            W2.html("a")
                                .class("mr-2")
                                .attribute("href",`/inventory/stocks/edit/${stock.id}`)
                                .content(W2.html("i").class("fas fa-pen"))
                        )
                        .content(
                            W2.html("i")
                                .class("fas fa-unlink text-danger")
                                .style("cursor","pointer")
                                .event("click",async ()=>{
                                    const response = await jsonPostAction("{{ route("inventory.removeStockFromCategory") }}",{
                                        category: category.id,
                                        stock: stock.id
                                    })

                                    if(!response.ok){
                                        BoostrapToast.open("Category","Failed to remove the stock from the category")
                                    } else {
                                        BoostrapToast.open("Category","Successfully removed the stock from the category")
                                    }

                                    app.categoryList.state.loadData()
                                })
                        )
                )
                //card body
                .content(
                    W2.html("img")
                        .attribute("src",`/inventory/stocks/icon/${stock.id}`)
                        .attribute("loading","lazy")
                        .attribute("alt",`Icons of the most important items in ${stock.name}`)
                        .style("width","100%")
                )
                .content(
                    W2.html("ul")
                        .class("list-group list-group-flush")
                        .content(stockCardPropertyEntry("Location",stock.location.name))
                        .content(stockCardPropertyEntry("Priority",stock.priority))
                        .content(stockCardPropertyEntry("Planned",stock.amount))
                        .content(stockCardPropertyEntry("Warning Threshold",stock.warning_threshold))
                        .content(stockCardPropertyEntry("Available",available,availabilityColor))
                        .content(stockCardPropertyEntry("Contracts",stock.available_on_contracts))
                        .content(stockCardPropertyEntry("Corporation Hangar",stock.available_in_hangars))
                )
        }

        function categoryComponent(app,category,collapsed,toggleCollapse) {
            return W2.html("div")
                        .class("card")
                        .content(
                            W2.html("div")
                                .class("card-body")
                                .content(
                                    //header
                                    W2.html("div")
                                        .class("d-flex align-items-baseline")
                                        .content(
                                            W2.html("h5")
                                                .class("card-title flex-grow-1")
                                                .content(category.name)
                                        )
                                        .content(
                                            W2.html("button")
                                                .class("btn btn-secondary mx-1")
                                                .content(
                                                    W2.html("i").class("fas fa-pen")
                                                )
                                                .event("click", () => editCategoryPopUp(app, category))
                                        )
                                        .contentIf(
                                            toggleCollapse,//only show expand button if expanding is supported
                                            W2.html("button")
                                                .class("btn btn-primary")
                                                .content(collapsed?"Expand":"Collapse")
                                                .event("click",(e)=>{
                                                    e.preventDefault()
                                                    toggleCollapse(category.id)
                                                })
                                        )
                                )
                                .contentIf(
                                    //stock cards
                                    !collapsed,
                                    W2.html("div")
                                        .class("d-flex flex-wrap")
                                        .content((container)=>{
                                            for (const stock of category.stocks) {
                                                container.content(stockCardComponent(app,stock,category))
                                            }
                                        })
                                )
                        )
        }

        function categoryListComponent(app) {
            class CategoryListState extends W2.W2MountState{
                categories
                location
                collapsed
                defaultCollapseState

                constructor() {
                    super();
                    this.categories = null
                    this.location = null
                    this.collapsed = {}
                    this.defaultCollapseState = true
                    this.loadData()
                }

                setLocation(location){
                    this.location = location
                    this.loadData()
                }

                toggleCollapse(id){
                    this.collapsed[id] = !this.collapsed[id]
                    this.stateChanged()
                }

                collapseAll(){
                    this.collapsed = {}
                    this.defaultCollapseState = true
                    this.stateChanged()
                }

                expandAll(){
                    this.defaultCollapseState = false
                    for (const category of this.categories) {
                        this.collapsed[category.id] = false
                    }
                    this.stateChanged()
                }

                isCollapsed(id){
                    const state = this.collapsed[id]

                    if(state===undefined) {
                        this.collapsed[id] = this.defaultCollapseState
                        return this.defaultCollapseState
                    }

                    return state
                }

                async loadData(){
                    let url = "{{ route("inventory.getCategories") }}"
                    if (this.location) {
                        url = `{{ route("inventory.getCategories") }}?location=${this.location}`
                    }

                    const response = await fetch(url)
                    if (!response.ok) {
                        BoostrapToast.open("Categories","Failed to load category data")
                        this.categoryData = null
                    }

                    this.categories = await response.json()

                    this.stateChanged()
                }
            }

            const state = new CategoryListState()

            return W2.mount(state,(container, mount,state)=>{
                if(state.categories){
                    for (const category of state.categories) {
                        container.content(
                            categoryComponent(
                                app,
                                category,
                                state.isCollapsed(category.id),
                                (id)=>state.toggleCollapse(id)
                            )
                        )
                    }
                } else {
                    container.content("Loading...")
                }
            })
        }

        function toolButtonPanelComponent(app) {
            return W2.html("div")
                .class("d-flex flex-row align-items-center mb-3")
                .content(
                    W2.html("button")
                        .class("btn btn-secondary ml-auto")
                        .content("Collapse All")
                        .event("click", () => {
                            app.categoryList.state.collapseAll()
                        })
                )
                .content(
                    W2.html("button")
                        .class("btn btn-secondary ml-1")
                        .content("Expand All")
                        .event("click", () => {
                            app.categoryList.state.expandAll()
                        })
                )
                .content(
                    W2.html("button")
                        .class("btn btn-primary ml-1")
                        .content(
                            W2.html("i").class("fas fa-plus"),
                            " Create Category"
                        )
                        .event("click", () => {
                            editCategoryPopUp(app,{})
                        })
                )
        }

        class App {
            categoryList
            locationFilter

            constructor() {
                this.categoryList = categoryListComponent(this)

                this.locationFilter = new LocationFilterComponent({
                    locationListeners: [(location) => {
                        this.categoryList.state.setLocation(location)
                    }]
                })
            }

            render(){
                return W2.emptyHtml()
                    .content(this.locationFilter.mount())
                    .content(toolButtonPanelComponent(this))
                    .content(this.categoryList)
            }
        }

        new App().render().addInto("content-target")
    </script>
@endpush



@push("head")
    <style>
        .stock-list-entry:hover {
            background-color: #eee;
        }

        .select2-container {
            width: 100% !important;
        }

        .toast {
            background-color: white;
        }
    </style>
@endpush