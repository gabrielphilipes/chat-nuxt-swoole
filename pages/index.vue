<template>
    <div class="container">
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card chat-app">
                    <div
                        id="plist"
                        class="people-list"
                    >
                        <ul class="list-unstyled chat-list mt-2 mb-0 space-y-2">
                            <ChatItemList
                                v-for="key in 3"
                                :key="key"
                                :active="key === 1"
                            />
                        </ul>
                    </div>

                    <div class="chat">
                        <div class="chat-header clearfix">
                            <ChatHeader />
                        </div>

                        <div class="chat-history">
                            <ul class="m-b-0">
                                <ChatMessage
                                    v-for="key in 3"
                                    :key="key"
                                    :receive="key === 1"
                                />
                            </ul>
                        </div>

                        <div class="chat-message clearfix">
                            <ChatForm />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
