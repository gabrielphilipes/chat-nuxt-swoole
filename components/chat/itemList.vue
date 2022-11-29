<script setup>
    const props = defineProps({
        active: { type: Boolean, default: false },
    })
</script>

<template>
    <li
        class="clearfix"
        :class="{ active: props.active }"
    >
        <img
            src="https://bootdey.com/img/Content/avatar/avatar2.png"
            alt="avatar"
        >
        <div class="about">
            <div class="name">
                Aiden Chavez
            </div>

            <div class="status">
                <i class="fa fa-circle online" /> online
            </div>
        </div>
    </li>
</template>
