<template>
    <div>
        <font-awesome-icon v-bind="$props">
            <slot></slot>
        </font-awesome-icon>
    </div>
</template>

<script>
export default {
    name: 'VcIcon',
    props: {
      icon: Array,
    }
};
</script>
