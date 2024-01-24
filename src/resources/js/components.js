function workspaceCreatorPopup(messages, ...updatedCallbacks) {
    const state = {
        name: ""
    }

    BootstrapPopUp.open(messages['create_workspace_dialog_title'], function (container, popup) {
        container.content(
            W2.html("div")
                .class("form-group")
                .content(
                    W2.html("label")
                        .content(messages['workspace_name_field']),
                    W2.html("input")
                        .attribute("type", "text")
                        .class("form-control")
                        .attribute("placeholder", messages['workspace_name_placeholder'])
                        .event("change", (e) => {
                            state.name = e.target.value
                        })
                ),
            W2.html("button")
                .class("btn btn-primary")
                .content(messages['workspace_create_btn'])
                .event("click", async () => {
                    if (state.name.length > 0) {
                        const response = await jsonPostAction("/inventory/workspaces/create", {
                            name: state.name
                        })

                        if (response.ok) {
                            BoostrapToast.open(messages['success_label'], messages['workspace_creation_success'])
                            popup.close()
                        } else {
                            BoostrapToast.open(messages['error_label'], messages['workspace_creation_error'])
                        }

                        for (const updatedCallback of updatedCallbacks) {
                            updatedCallback()
                        }
                    }
                })
        )
    })
}

function workspaceSelector(messages, ...callbacks) {
    const state = {
        workspaces: [],
        currentWorkspace: null
    }

    async function loadWorkspaces() {
        const response = await fetch("/inventory/workspaces/list")
        if (response.ok) {
            state.workspaces = await response.json()
        } else {
            BoostrapToast.open("Error", messages['error_load_workspace'])
        }
    }

    const changeWorkspace = (workspace) => {
        window.sessionStorage.setItem("selectedWorkspace", workspace.id)
        state.currentWorkspace = workspace
        for (const callback of callbacks) {
            callback(workspace)
        }
    }

    const mount = W2.mount(state, (container, mount, state) => {
        container.content(
            W2.html("div")
                .class("card")
                .content(
                    W2.html("div")
                        .class("card-header d-flex flex-row align-items-baseline")
                        .content(
                            W2.html("h3")
                                .class("cart-title")
                                .content(`${messages['select_workspace_title']} (${state.currentWorkspace ? state.currentWorkspace.name : ""})`),
                            W2.html("button")
                                .class("btn btn-success ml-auto")
                                .content(
                                    W2.html("i").class("fas fa-plus")
                                )
                                .event("click", () => {
                                    workspaceCreatorPopup(messages, async () => {
                                        await loadWorkspaces()
                                        mount.update()
                                    })
                                })
                        ),
                    W2.html("div")
                        .class("card-body")
                        .content(
                            W2.html("ul")
                                .class("list-group")
                                .content((container) => {
                                    if (state.workspaces.length === 0) {
                                        container.content(
                                            W2.html('li')
                                                .class("list-group-item d-flex flex-column align-items-center")
                                                .content(
                                                    W2.html('h4')
                                                        .class('mb-3')
                                                        .content(messages['empty_workspace_message']),
                                                    W2.html("button")
                                                        .class("btn btn-success")
                                                        .content(messages['create_new_workspace_hint'])
                                                        .event("click", () => {
                                                            workspaceCreatorPopup(messages,async () => {
                                                                await loadWorkspaces()
                                                                mount.update()
                                                            })
                                                        })
                                                )
                                        )
                                    }
                                    for (const workspace of state.workspaces) {
                                        container.content(
                                            W2.html("btn")
                                                .class("list-group-item list-group-item-action")
                                                .classIf(state.currentWorkspace && workspace.id === state.currentWorkspace.id, "active")
                                                .content(workspace.name)
                                                .event("click", () => {
                                                    changeWorkspace(workspace)
                                                    mount.update()
                                                })
                                        )
                                    }
                                })
                        )
                )
        )
    })

    loadWorkspaces().then(() => {
        let selectedWorkspaceID = window.sessionStorage.getItem("selectedWorkspace")
        if (selectedWorkspaceID) {
            selectedWorkspaceID = parseInt(selectedWorkspaceID)
            for (const workspace of state.workspaces) {
                if (workspace.id === selectedWorkspaceID) {
                    changeWorkspace(workspace)
                }
            }
        }

        mount.update()
    })

    return mount
}