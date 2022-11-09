@extends('web::layouts.grids.12')

@section('title', "Item Browser")
@section('page_header', "Item Browser")


@section('full')
    <div id="main"></div>
@stop

@push('javascript')
    <script src="@inventoryVersionedAsset('inventory/js/bootstrap-autocomplete.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/select2w2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/bootstrapW2.js')"></script>
    <script src="@inventoryVersionedAsset('inventory/js/components.js')"></script>


    <script>

        async function jsonPostAction(url, data) {
            return await fetch(url, {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data),
            })
        }

        function confirmButtonComponent(text, callback) {
            const state = {
                firstStep: true
            }
            return W2.mount(state, (container, mount, state) => {
                if (state.firstStep) {
                    container.content(
                        W2.html("button")
                            .class("btn btn-danger")
                            .content(text)
                            .event("click", () => {
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
                                    .event("click", () => {
                                        state.firstStep = true
                                        mount.update()
                                    })
                            )
                            .content(
                                W2.html("button")
                                    .class("btn btn-warning")
                                    .content("Confirm")
                                    .event("click", () => {
                                        callback()
                                        state.firstStep = true
                                        mount.update()
                                    })
                            )
                    )
                }
            })
        }

        const appState = {
            corporations: [],
            alliances: [],
            corporationSelector: null,
            allianceSelector: null,
        }

        async function fetchData() {
            let response = await fetch("{{ route("inventory.listCorporations") }}")
            appState.corporations = await response.json()
            response = await fetch("{{ route("inventory.listAlliances") }}")
            appState.alliances = await response.json()
        }

        const mount = W2.mount(appState, (container, mount, state)=>{
            //card for alliances
            container.content(W2.html("div")
                .class("card")
                .content(
                    //title header
                    W2.html("div")
                        .class("card-header")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Alliances")
                        ),
                    //card body
                    W2.html("div")
                        .class("card-body")
                        .content(
                            W2.html("div")
                                .class("form-group d-flex flex-column w-100")
                                .content(
                                    W2.html("label")
                                        .attribute("for", W2.getID("addAlliance", true))
                                        .content("Add alliances"),
                                    select2Component({
                                        select2: {
                                            placeholder: "Select an alliance",
                                            ajax: {
                                                url: "{{ route("inventory.allianceLookup") }}"
                                            },
                                            allowClear: true,
                                        },
                                        selectionListeners: [
                                            (selection) => {
                                                state.allianceSelector = selection
                                                mount.update()
                                            }
                                        ],
                                        selection: state.allianceSelector,
                                        id: W2.getID("addAlliance")
                                    }),
                                ).contentIf(state.allianceSelector!==null,
                                W2.html("button")
                                    .class("btn btn-primary btn-block mt-2")
                                    .content("Add")
                                    .event("click",async ()=>{
                                        const response = await jsonPostAction("{{ route("inventory.addAlliance") }}",{
                                            alliance_id: state.allianceSelector.id
                                        })

                                        if (response.ok){
                                            BoostrapToast.open("Success",`Successfully added ${state.allianceSelector.text}`)
                                        } else {
                                            BoostrapToast.open("Error",`Failed to add ${state.allianceSelector.text}`)
                                        }

                                        await fetchData()
                                        mount.update()
                                    })
                            ),
                            W2.html("ul")
                                .class("list-group")
                                .content(
                                    (container) => {
                                        for (const alliance of appState.alliances) {
                                            container.content(
                                                W2.html("li")
                                                    .class("list-group-item d-flex flex-row justify-content-between align-items-baseline")
                                                    .content(
                                                        W2.html("span")
                                                            .content(alliance.alliance.name),
                                                        W2.html("div")
                                                            .contentIf(!alliance.manage_members,
                                                                tooltipComponent(
                                                                    W2.html("button")
                                                                        .class("btn btn-secondary mx-1")
                                                                        .content("Add Members")
                                                                        .event("click",async ()=>{
                                                                            const response = await jsonPostAction("{{ route("inventory.addAllianceMembers") }}",{
                                                                                alliance_id: alliance.alliance_id
                                                                            })

                                                                            if (response.ok){
                                                                                BoostrapToast.open("Success",`Successfully added members of ${alliance.alliance.name}`)
                                                                            } else {
                                                                                BoostrapToast.open("Error",`Failed to add members of ${alliance.alliance.name}`)
                                                                            }

                                                                            await fetchData()
                                                                            mount.update()
                                                                        }),
                                                                    "Existing and new alliance members will be added automatically.")
                                                            ).contentIf(alliance.manage_members,
                                                                tooltipComponent(
                                                                    W2.html("button")
                                                                        .class("btn btn-secondary mx-1")
                                                                        .content("Remove Members")
                                                                        .event("click",async ()=>{
                                                                            const response = await jsonPostAction("{{ route("inventory.removeAllianceMembers") }}",{
                                                                                alliance_id: alliance.alliance_id
                                                                            })

                                                                            if (response.ok){
                                                                                BoostrapToast.open("Success",`Successfully removed members of ${alliance.alliance.name}`)
                                                                            } else {
                                                                                BoostrapToast.open("Error",`Failed to add members of ${alliance.alliance.name}`)
                                                                            }

                                                                            await fetchData()
                                                                            mount.update()
                                                                        }),
                                                                    "Automatically added corporations will be removed and no new corporations will be added in the future. Manually added corporations will stay.")
                                                            ).content(
                                                                confirmButtonComponent("Remove",async ()=>{
                                                                    const response = await jsonPostAction("{{ route("inventory.removeAlliance") }}",{
                                                                        alliance_id: alliance.alliance_id
                                                                    })

                                                                    if (response.ok){
                                                                        BoostrapToast.open("Success",`Successfully removed ${alliance.alliance.name}`)
                                                                    } else {
                                                                        BoostrapToast.open("Error",`Failed to remove ${alliance.alliance.name}`)
                                                                    }

                                                                    await fetchData()
                                                                    mount.update()
                                                                })
                                                            )
                                                    )
                                            )
                                        }
                                    }
                                )
                        )
                )
            )

            //card for corporations
            container.content(W2.html("div")
                .class("card")
                .content(
                    //title header
                    W2.html("div")
                        .class("card-header")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content("Corporations")
                        ),
                    //card body
                    W2.html("div")
                        .class("card-body")
                        .content(
                            W2.html("div")
                                .class("form-group d-flex flex-column w-100")
                                .content(
                                    W2.html("label")
                                        .attribute("for", W2.getID("addCorporation", true))
                                        .content("Add corporations"),
                                    select2Component({
                                        select2: {
                                            placeholder: "Select a corporation",
                                            ajax: {
                                                url: "{{ route("inventory.corporationLookup") }}"
                                            },
                                            allowClear: true,
                                        },
                                        selectionListeners: [
                                            (selection) => {
                                                state.corporationSelector = selection
                                                mount.update()
                                            }
                                        ],
                                        selection: state.corporationSelector,
                                        id: W2.getID("addCorporation")
                                    }),
                                ).contentIf(state.corporationSelector!==null,
                                    W2.html("button")
                                        .class("btn btn-primary btn-block mt-2")
                                        .content("Add")
                                        .event("click",async ()=>{
                                            const response = await jsonPostAction("{{ route("inventory.addCorporation") }}",{
                                                corporation_id: state.corporationSelector.id
                                            })

                                            if (response.ok){
                                                BoostrapToast.open("Success",`Successfully added ${state.corporationSelector.text}`)
                                            } else {
                                                BoostrapToast.open("Error",`Failed to add ${state.corporationSelector.text}`)
                                            }

                                            await fetchData()
                                            mount.update()
                                        })
                                ),
                            W2.html("ul")
                                .class("list-group")
                                .content(
                                    (container) => {
                                        for (const corporation of appState.corporations) {
                                            container.content(
                                                W2.html("li")
                                                    .class("list-group-item d-flex flex-row justify-content-between align-items-baseline")
                                                    .content(
                                                        W2.html("span")
                                                            .content(corporation.corporation.name),
                                                        confirmButtonComponent("Remove",async ()=>{
                                                            const response = await jsonPostAction("{{ route("inventory.removeCorporation") }}",{
                                                                corporation_id: corporation.corporation_id
                                                            })

                                                            if (response.ok){
                                                                BoostrapToast.open("Success",`Successfully removed ${corporation.corporation.name}`)
                                                            } else {
                                                                BoostrapToast.open("Error",`Failed to remove ${corporation.corporation.name}`)
                                                            }

                                                            await fetchData()
                                                            mount.update()
                                                        })
                                                    )
                                            )
                                        }
                                    }
                                )
                        )
                )
            )
        })

        fetchData().then(()=>{
            mount.update()
        })

        mount.addInto("main")
    </script>
@endpush