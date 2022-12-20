@extends('web::layouts.grids.12')

@section('title', "Item Browser")
@section('page_header', "Item Browser")


@section('full')

    <div id="main"></div>
@stop

@push('javascript')
    <script>const CSRF_TOKEN = '{{ csrf_token() }}'</script>
    <script src="@inventoryVersionedAsset('inventory/js/utils.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/select2w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/bootstrapW2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/components.js')"></script>


    <script>

        const appState = {
            locationFilter: null,
            itemFilter: null,
            items: [],
            next_page: 0,
            workspace: null
        }

        async function fetchData(location, item, reset_page=true) {
            if (reset_page) {
                appState.next_page = 0
                appState.items = []
            }

            if(appState.workspace) {
                const data = await jsonPostAction("{{ route("inventory.itemBrowserData") }}", {
                    location,
                    item,
                    page: appState.next_page++,
                    workspace: appState.workspace.id
                })

                appState.items = appState.items.concat(await data.json())
            }
        }

        const mount = W2.mount(appState, (container, mount, state)=>{
            //card
            container.contentIf(state.workspace,W2.html("div")
                .class("card")
                .content(
                    //title header
                    W2.html("div")
                        .class("card-header")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Item Browser")
                        ),
                    //card body
                    W2.html("div")
                        .class("card-body")
                        .content(
                            //location
                            W2.html("div")
                                .class("form-group d-flex flex-column")
                                .content(
                                    W2.html("label")
                                        .content("Location")
                                        .attribute("for",W2.getID("filterLocation"))
                                ).content(
                                    select2Component({
                                        select2: {
                                            placeholder: "All locations",
                                            ajax: {
                                                url: "{{ route("inventory.locationLookup") }}"
                                            },
                                            allowClear: true
                                        },
                                        selectionListeners: [
                                            async (selection) => {
                                                state.locationFilter = selection
                                                mount.update()

                                                await fetchData(state.locationFilter?state.locationFilter.id:null,state.itemFilter?state.itemFilter.id:null)
                                                mount.update()
                                            }
                                        ],
                                        id: W2.getID("filterLocation"),
                                        selection: state.locationFilter
                                    })
                                ),
                            //item
                            W2.html("div")
                                .class("form-group d-flex flex-column")
                                .content(
                                    W2.html("label")
                                        .content("Item")
                                        .attribute("for",W2.getID("filterItem"))
                                ).content(
                                select2Component({
                                    select2: {
                                        placeholder: "All Items",
                                        ajax: {
                                            url: "{{ route("inventory.itemLookup") }}"
                                        },
                                        allowClear: true
                                    },
                                    selectionListeners: [
                                        async (selection) => {
                                            state.itemFilter = selection
                                            mount.update()

                                            await fetchData(state.locationFilter?state.locationFilter.id:null,state.itemFilter?state.itemFilter.id:null)
                                            mount.update()
                                        }
                                    ],
                                    id: W2.getID("filterItem"),
                                    selection: state.itemFilter
                                })
                            )
                        )
                ))

            container.contentIf(state.items.length > 0 && state.workspace,
                W2.html("div")
                    .class("card")
                    .content(
                        W2.html("div")
                            .class("card-body")
                            .content(
                                W2.html("ul")
                                    .class("list-group")
                                    .content((container)=>{
                                        for (const item of state.items){
                                            container.content(
                                                W2.html("li")
                                                    .class("list-group-item d-flex flex-row align-items-center")
                                                    .content(
                                                        W2.html("img")
                                                            .class("img-circle")
                                                            .attribute("src",`https://images.evetech.net/types/${item.type_id}/icon?size=32`),
                                                        W2.html("span")
                                                            .class("ml-2")
                                                            .content(`${item.amount}x ${item.type.typeName}`),
                                                        W2.html("span")
                                                            .class("text-muted ml-auto")
                                                            .content(`${item.source.source_name} @ ${item.source.location.name}`)
                                                    )
                                            )
                                        }
                                    }),
                            ).contentIf(state.items.length % 100 === 0 && state.items.length > 0,
                                W2.html("div")
                                    .class("d-flex flex-row justify-content-center mt-2")
                                    .content(
                                        W2.html("button")
                                            .class("btn btn-primary")
                                            .content("Load More")
                                            .event("click",async ()=>{
                                                await fetchData(state.locationFilter?state.locationFilter.id:null,state.itemFilter?state.itemFilter.id:null,false)
                                                mount.update()
                                            })
                                    )
                            )
                    )
            )
        })

        fetchData(null,null).then(()=>{
            mount.update()
        })

        W2.emptyHtml()
            .content(workspaceSelector(async (workspace) => {
                appState.workspace = workspace
                await fetchData(appState.locationFilter?appState.locationFilter.id:null,appState.itemFilter?appState.itemFilter.id:null, true)
                mount.update()
            }))
            .content(mount)
            .addInto("main")
    </script>
@endpush