<style scoped>

</style>



<template>

    <div>

        <div>
            Available calendars
            <ul>
                <li v-for="calendar in calendarList.data.items" :key="calendar.id"> {{ calendar.summary }} </li>
            </ul>
        </div>

        <div>
            
            <form v-if="this.doUpdate == false" id="select_google_calendar" class="form-inline my-2 my-lg-0" method="POST" :action="actionPath" >
            <!-- the create version. -->
                <input type="hidden" name="_token" :value="csrf"></input>
                <input type="hidden" name="aspect_type" :value="aspectType"></input>
                <input type="hidden" name="subject_id" :value="subjectId"></input>
                <input type="hidden" name="aspect_data" value=""></input>
                <input type="hidden" name="hidden" value="0"></input>
                <input type="hidden" name="aspect_source" value="Google"></input>

                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title"></input>        
                </div>

                <div class="form-group">
                    <label for="settings_calendar_id">Use this calendar</label>
                    <select class="form-control" id="settings_calendar_id" name="settings_calendar_id">
                        <option disabled value=""> -- Select a calendar -- </option>
                        <option v-for="calendar in calendarList.data.items" :key="calendar.id" v-bind:value="calendar.id"> {{ calendar.summary }} </option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
            
        </div>

    </div>

</template>



<script>
    export default {
        components: {},
        mixins: [],
        data () {
            return {
                csrf: '',
                calendarList: '',
                calendarId: ''
            }
        },
        mounted() {
            this.csrf = window.axios.defaults.headers.common['X-CSRF-TOKEN'];
            this.fetchCalendars();
        },
        props: [
            'aspectId',
            'aspectType',
            'aspectNotes',
            'aspectData',
            'aspectSource',
            'hidden',
            'subjectId',
            'settingsCalendarId',
            'title'
        ],
        computed: {
            doUpdate: function(){
                if (typeof this.aspectId === 'undefined' || this.aspectId === null) {
                    return false;
                } else {
                    return true;
                }
            },
            actionPath: function(){
                if ( this.doUpdate ){
                    var action_path = '/aspect/' + this.aspectId + '/edit';
                    return action_path;
                } else {
                    return '/aspect/create';
                }
            }
        },
        methods: {
            fetchCalendars(){
                var self=this;
                axios.get('/gcal/available_calendars')
                    .done(function(response){
                        self.calendarList = response;
                        console.log(response);
                    })
                    .error(function(error){
                        console.log(error);
                    });

            },
            createCalendar(){

            },
            createAspect(){

            }

        }
    };

</script>