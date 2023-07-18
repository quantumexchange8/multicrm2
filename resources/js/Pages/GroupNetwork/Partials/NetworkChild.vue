<script setup>


defineProps({
    children: Array,
});

</script>

<template>
    
        <div class="dd-content box-bg-color">
            <div v-for="child in children" class="text-gray-500 dark:text-white">
                <div v-for="subchild in child">
                    {{ subchild.upline_id + ' | ' + subchild.first_name + ': ' + subchild.email + ' | Ranking: ' + subchild.role }}
                </div>
                
                
            </div>
        </div>
        <template v-if="child">
            
                <sub-sponsor v-for="grandchild in child" :key="grandchild.id" :child="grandchild" />
            
        </template>
    
    
</template>