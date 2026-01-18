/**
 * @jQuery      plugin
 *
 * search artistPickerSimple
 *
 * @date        December 2021
 * @copyright   Believe music
 * @version     1
 */

function verifyArtistId(that, live = false) {
    var store = $(that).data('store')
    var value = $(that).val()
    var error = false
    var errorText = ''
    var regex = ''

    switch (store) {
        case 204:
            regex = /(^(https?:\/\/)?open\.spotify\.com\/artist\/([a-zA-Z0-9]+)|^spotify:artist:([a-zA-Z0-9]+)$)/
            break
        case 0:
            regex = /^(https?:\/\/)?music\.apple\.com\/[a-z]{2}\/artist\/([^\/]+\/)?([0-9]+)/
            break
        default :
            regex = /^(https?:\/\/)?(www|music)\.youtube\.com\/channel\/([\w\-]+)/
    }
    var matches = value.match(regex)

    if (matches) {
        switch (store) {
            case 204 :
                var valueMatched = matches[3] ? 3 : 4
                break
            case 0 :
                var valueMatched = 3
                break
            default :
                var valueMatched = 3
        }
        value = matches[valueMatched]
        $(that).val(value)
    }

    if (value) {
        if (204 === store) {
            var regex = /^[a-z0-9]+$/i
            if (!regex.test(value) || value.length != 22) {
                errorText = EE.EASYENTRY_SPOTIFY_ARTIST_ID_CONTROL
                error = true
            }
        } else if (0 === store) {
            var regex = /^[0-9]+$/
            if (!regex.test(value) || value.length > 14) {
                errorText = EE.EASYENTRY_ITUNES_ARTIST_ID_CONTROL
                error = true
            }
        } else if (334 === store) {
            var regex = /^\S+$/i
            if (!regex.test(value) || value.length != 24) {
                errorText = SAID.EASYENTRY_YOUTUBE_ARTIST_ID_CONTROL
                error = true
            }

            if (!error && live) {
                $(that).parents('.searchContent.youtube').find('.button-ok').hide()
                $.post(
                    '/easyentry/artistid/validateyoutubechannel',
                    { 'channelId': value }
                ).then(function(response) {
                    if (response.hasError === undefined || response.hasError === true) {
                        $(that).parent().find('.errorpagename').text(response.hasError === true ? response.errorText : EEAP.EEAP_A_PROBLEM)
                    } else {
                        $(that).parents('.searchContent.youtube').find('.button-ok').show()
                    }
                })
            }
        }
    }

    if (
        false === error
        && Array.isArray(window.currentArtistIds)
        && -1 !== window.currentArtistIds.indexOf(value)
    ) {
        errorText = EE.EASYENTRY_ARTIST_ID_ALREADY_ASSOCIATED
        error = true
    }

    $(that).parent().find('.errorpagename').text(errorText)

    if (error) {
        return false
    }

    return true
}

function autoCheckCheckbox(that) {
    $(that).parent().parent().find('input[type=radio]').first().prop('checked', true)
    verifyArtistId(that, true)
}

function checkboxByRow(that) {
    $(that).find('input[type=radio]').prop('checked', true)
}

(function($) {

    $.fn.artistPickerSimple = function(options) {
        // DÃ©clarations de valeurs par dÃ©faut
        var settings = $.extend({
            urlAllContributors: 'contributor/artistpicker/blvSearch/',
            urlAllExactContributors: 'contributor/artistpicker/blvSearchExact/',
            urlContributor: 'contributor/artistpicker/getContributorById/',
            urlContribInsert: 'contributor/artistpicker/createNewContributor/',
            urlContribUpdate: 'contributor/artistpicker/updateContributor/',
            urlApiStores: 'contributor/artistpicker/searchByName/',
            dispoStoreIds: { 0: 'iTunes', 204: 'Spotify', 334: 'Youtube' },
            urlStore: {
                0: 'https://music.apple.com/us/artist/',
                204: 'https://open.spotify.com/artist/',
                334: 'https://www.youtube.com/channel/'
            },
            regexStore: {
                0: /^[0-9]{1,14}$/,
                204: /^[a-z0-9]{22}$/i,
                334: /^\S{24}$/i
            },
            dispoIds: [0, 204, 334],
            nameInputForm: 'idContributor',
            idProducer: 0,
            assetType: 'pendingAlbum',
            idAsset: 0,
            showEmpty: true,
            albumType: 'final'
        }, options)

        var selector = $(this)

        if (!selector.length) {
            return
        }

        if (selector.attr('name') == undefined || selector.attr('name') == '') {
            selector.attr('name', 'artistPicker-' + uniqIdArtistPicker)
        }

        var selectorName = 'artistPicker-' + (selector.attr('id'))

        var splittedId = selector.attr('id').split('-')
        var position = splittedId.length == 3 ? splittedId[2] : '0'
        var infoContribType = splittedId.length == 3 ? splittedId[1] : 'releaseArtist'
        var contribType = infoContribType.includes('Artist') ? 'artists' : 'featuring'

        localStorage.setItem('listeStoresCalled' + selectorName, JSON.stringify([]))
        selector.attr('autocomplete', 'off')
        //setup before functions
        var typingTimer                //timer identifier
        var doneTypingInterval = 1000  //time in ms, 1 second

        //on keyup, start the countdown
        selector.on('keyup', function() {
            clearTimeout(typingTimer)
            typingTimer = setTimeout(searchArtistPicker, doneTypingInterval)
        })

        //on keydown, clear the countdown
        selector.on('keydown', function() {
            clearTimeout(typingTimer)
        })

        if (settings.hasOwnProperty('variousArtistsContributorId') && false != settings.variousArtistsContributorId) {
            selector.on('change', function() {
                if ('Various Artists' === selector.val()) {
                    searchArtistPickerById(settings.variousArtistsContributorId, settings.albumType)
                }
            })
        } else {
            console.error('Failed to get "Various Artists" contributor id from settings!')
        }

        // Init appel:
        var idContributor = -1
        if (typeof contributors !== 'undefined' && contributors.length > 0) {
            if (contributors.findIndex(c => !c.contributorTypeName) != -1) {
                apiErrorMessage()
                return
            }

            const index = contributors.findIndex(
                c => c.contributorTypeName === contribType
                    && c.position === position
            )

            if (index !== -1) {
                idContributor = contributors[index].idContributor
                contributors.splice(index, 1)
            }
        }

        if (idContributor > 0 && $.isNumeric(idContributor)) {
            searchArtistPickerById(idContributor, 'pending')
        } else if ($.trim(selector.val()) != '') {
            setTimeout(function() {
                clearTimeout(typingTimer)
            }, 250)
            searchByName()
        }

        function getUrlStore(storeId) {
            if (storeId === '0' && window.producerCountry && window.producerCountry === 'CN') {
                return 'https://music.apple.com/cn/artist/'
            } else {
                return settings.urlStore[storeId]
            }
        }

        function searchByName() {
            var data = getExactContrib()
            localStorage.setItem('listeStoresCalled' + selectorName, JSON.stringify([]))
            if ($('input[name="' + settings.nameInputForm + '"]').length) {
                $('input[name="' + settings.nameInputForm + '"]').val('')
            }
            $('.alert-danger_' + selectorName).remove()
            if (data['data'].length === 1) {
                if (null !== data['data'][0].pending_id) {
                    showContributor(data['data'][0])
                } else {
                    searchArtistPickerById(data['data'][0].id)
                }
            } else {
                searchArtistPicker()
            }
        }

        /**
         * destroy plugin
         * @param string selectorName
         * @return void
         */
        function destroy(selectorName) {
            $('#content_' + selectorName).remove()

        }

        /**
         * init event to button
         * @param string selectorName
         * @return void
         */
        function init(selectorName) {
            attachClick(selectorName)
        }

        function showError(message) {
            $('.' + selectorName).remove()
            $('.' + selectorName + '_msgNoInBd').remove()
            $('#content_' + selectorName).remove()
            insertLigneErrorAfterSelector(selectorName, message)
        }

        function apiErrorMessage() {
            selector.prop('disabled', true);
            $('.' + selectorName).remove()
            $('.' + selectorName + '_msgNoInBd').remove()
            $('#content_' + selectorName).remove()
            selector.after('<div class="content-main artistPickerInError"  id="content_' + selectorName + '">' + EEAP.EEAP_API_FAILED + '</div>')
        }

        /**
         * search Artist Picker
         * @return void
         */
        function searchArtistPicker() {
            if (selector.val().trim().includes(',')) {
                showError(EEAP.EEAP_NO_COMMA)
                return
            }

            var data = getData()

            if (!data.available) {
                showError(EEAP.EEAP_UNAVAILABLE)
                return
            }
            detachToInput()
            localStorage.setItem('contrib' + selectorName, JSON.stringify(data['data']))
            localStorage.setItem('listeStoresCalled' + selectorName, JSON.stringify([]))
            if ($('input[name="' + settings.nameInputForm + '"]').length) {
                $('input[name="' + settings.nameInputForm + '"]').val('')
            }
            $('.alert-danger_' + selectorName).remove()
            if (data['data'].length > 0) {
                var returnStoreId = getAllContributorInfo(data['data'])
                $('.' + selectorName).remove()
                $('.' + selectorName + '_msgNoInBd').remove()
                $('#content_' + selectorName).remove()

                selector.after('<div class="content-main"  id="content_' + selectorName + '"><div class="' + selectorName + ' list-storeIds"><ul class=""></ul><a class="artistNotExist">' + EEAP.EEAP_ADD_ARTIST + '</a></div></div>')
                $.each(returnStoreId, function(k, data) {
                    addContributorInfoToList(data)
                })
                $('.' + selectorName).after(showButtonSearch(selectorName))
                init(selectorName)
            } else {
                addNotInDbMsg(selectorName)
            }
        }

        function createNewContrib() {
            var nameContib = selector.val()
            var storeIds = {}
            var response = false

            response = createNewContib(nameContib, settings.idProducer, storeIds)
            if (response['data']) {
                $('#content_' + selectorName).remove()
                $('.selected-main_insert_' + selectorName).remove()
                $('#selectedList' + selectorName).remove()
                showContributor(response['data'])
            } else {
                insertLigneErrorAfterSelector(seletorName, response['error'])
            }
        }

        /**
         * init event to button
         * @param string selectorName
         * @return void
         */
        function attachClick(selectorName) {
            // button_ok
            $('#content_' + selectorName).on('click', '#button-ok_' + selectorName, function(e) {
                var selectorType = $('input[name=\'storeselected\']:checked').attr('data-type')
                var idProducer = settings.idProducer
                var idStore = $('input[name=\'storeselected\']:checked').attr('data-idStore')
                var seletorName = $('input[name=\'storeselected\']:checked').attr('data-seletorName')
                var itemSelct = selector
                var mode = 'edit'
                var idcontrib = $('#content_' + selectorName).find('div.editBySotreId').attr('data-idcontrib')

                if (typeof idcontrib == 'undefined') {
                    mode = 'add'
                }

                $('.alert-danger_' + seletorName).remove()
                $('.selected-main_' + seletorName).remove()
                $('#selectedList' + seletorName).remove()
                $('#ulMsgNotInDb').remove()

                if (typeof selectorType == 'undefined') {
                    return false
                }

                switch (selectorType) {
                    case 'nothing':
                        var nameContib = itemSelct.val()
                        var storeIds = {}
                        var idContributor = ''
                        var img = ''
                        // mode = 'add';
                        break
                    case 'input':
                        var namepage = $('#default-value_' + idStore + selectorName + ' input[name=\'pageName_' + idStore + '\']').val()

                        if ($.trim(namepage) === '') {
                            $('#default-value_' + idStore + selectorName + ' input[name=\'pageName_' + idStore + '\']').parent().find('.errorpagename').text(EEAP.EEAP_REQUIRED_FIELD)
                            return false
                        }

                        if (!verifyArtistId('#default-value_' + idStore + selectorName + ' input[name=\'pageName_' + idStore + '\']')) {
                            return false
                        }

                        namepage = $('#default-value_' + idStore + selectorName + ' input[name=\'pageName_' + idStore + '\']').val()

                        var nameContib = itemSelct.val()
                        var seletorName = $('#default-value_' + idStore + selectorName + ' input[name=\'storeselected\']:checked').attr('data-seletorName')
                        var storeIds = [{ 'id': idStore, 'store_id': namepage }]
                        var idContributor = namepage
                        var img = ''
                        break
                    case 'select':
                        if ('ok_' + $('input[name=\'storeselected\']:checked').attr('data-idStore') == $(this).attr('name')) {
                            var nameContib = $('input[name=\'storeselected\']:checked').attr('data-name')
                            var img = $('input[name=\'storeselected\']:checked').attr('data-img')
                            var idContributor = $('input[name=\'storeselected\']:checked').attr('data-idContributor')
                            var idStore = $('input[name=\'storeselected\']:checked').attr('data-idStore')
                            var storeIds = [{ 'id': idStore, 'store_id': idContributor }]
                            selector.val(nameContib)
                            break
                        }
                        break
                }

                var response = false
                if ($(this).attr('id').indexOf(selectorName) >= 0) {
                    if ('edit' == mode) {
                        response = updateContib(idcontrib, storeIds)
                    } else {
                        response = createNewContib(nameContib, idProducer, storeIds)
                    }


                    if (response['data']) {
                        $('#content_' + seletorName).remove()
                        if (null !== response['data'].pending_id) {
                            showContributor(response['data'])
                        } else {
                            searchArtistPickerById(response['data'].id)
                        }
                    } else {
                        insertLigneErrorAfterSelector(seletorName, response['error'])
                    }
                }


                $('.searchContent').hide()
                $('.searchNotInDb').hide()

                if (!$('.msgNoInBd').html()) {
                    $('.selected-storeId').after(showButtonSearch(selectorName))
                }

            })
            // button_cancel
            $(document).on('click', '#button-cancel', function(e) {
                $('.searchContent').hide()
            })
            // edit
            $('#content_' + selectorName + ', #selectedList' + selectorName).on('click', '.editBySotreId, .searchNotInDb', function(e) {

                $(this).off('click')
                var selName = $(this).attr('data-title')
                var mode = $(this).attr('data-mode')
                var idContrib = $(this).attr('data-idContrib')
                var pendingid = $(this).attr('data-pendingid')
                var idDiv = $(this).attr('id') + 'Content' + selName
                var idStore = getStoreIdByName(idDiv)
                var contibName = selector.val()

                if ('add' == mode) {
                    idContrib = ''
                }

                if ($('#mode_' + selectorName).length == 0) {
                    $('#searchOtherBlv2_' + idStore + selectorName).after('<input type="hidden" value="' + mode + '" data-idcontrib="' + idContrib + '" id="mode_' + selectorName + '">')
                } else {
                    $('#mode_' + selectorName).val(mode)
                    $('#mode_' + selectorName).attr('data-idcontrib', idContrib)
                }

                var a = []
                a = JSON.parse(localStorage.getItem('listeStoresCalled' + selectorName)) || []

                if (idStore && ($.inArray(idStore, a) == -1)) {
                    a.push(idStore)
                    localStorage.setItem('listeStoresCalled' + selName, JSON.stringify(a))

                    var dataSearch = searchInApiStores(contibName, idStore)

                    if (dataSearch['data'].length > 0) {
                        addListStorieContent(dataSearch['data'], idStore, selName, mode)
                    } else {
                        addLineInListeStoeir(idStore, selName, mode)
                    }
                }
                $('.searchContent').hide()
                $('#' + idDiv).show('slow')
            })

            $('#content_' + selectorName + ', #selectedList' + selectorName).on('click', '.deleteContributor', function(e) {
                $('#content_' + selectorName).remove()
                selector.show()
                detachToInput()
                selector.val('')
                selector.blur()
            })

            $('#content_' + selectorName).on('click', '.artistNotExist', function(e) {
                createNewContrib()
            })

            $('#content_' + selectorName + ', #selectedList' + selectorName).on('click', '.infos', function(e) {
                selector.toggle()
            })

            $('#content_' + selectorName).on('click', '.idContributorAction', function(e) {
                var idContributor = $(this).attr('data-value')
                var inputName = $(this).attr('data-selector')

                $('.selected-main_insert_' + selectorName).remove()
                $('.' + inputName).remove()
                $('.selectedList' + inputName).remove()
                $('.selected-main_' + inputName).remove()
                $('#selectedList' + selectorName).remove()
                var contribs = JSON.parse(localStorage.getItem('contrib' + selectorName)) || []
                var contrib = contribs.find(e => e.id === idContributor)
                if (contrib !== undefined) {
                    if (null !== contrib.pending_id) {
                        showContributor(contrib)
                    } else {
                        searchArtistPickerById(contrib.id)
                    }
                } else {
                    searchArtistPickerById(idContributor)
                }
            })
            selector.show()
        }

        function attachToInput(idContrib) {
            $(selector).data('idcontrib', idContrib)
        }

        function detachToInput() {
            $(selector).removeData('idcontrib')
        }

        function showContributor(dataContrib) {
            localStorage.setItem('listeStoresCalled' + selectorName, JSON.stringify([]))
            localStorage.setItem('contrib' + selectorName, JSON.stringify([]))

            if (dataContrib) {
                $('.' + selectorName).remove()
                $('.' + selectorName + '_msgNoInBd').remove()
                $('#content_' + selectorName).remove()
                var contributorId = dataContrib.id
                var contributorPendingId = dataContrib.pending_id
                attachToInput(dataContrib.pending_id)

                var contributorName = dataContrib.name
                if ($('input[name="' + settings.nameInputForm + '"]').length) {
                    $('input[name="' + settings.nameInputForm + '"]').val(contributorPendingId)
                } else {
                    selector.parent().append(
                        $('<input>', {
                            type: 'hidden',
                            name: settings.nameInputForm,
                            val: contributorPendingId
                        })
                    )
                }
                selector.val(contributorName)
                selector.after('<div class="content-main" id="content_' + selectorName + '"><div class="' + selectorName + ' selected-storeId"><ul class=""></ul></div></div>')
                var img = ''
                $.each(dataContrib, function(m, store) {
                    if ($.isArray(store)) {
                        $.each(store, function(k, stor) {
                            if (stor.store_image) {
                                img = `<img src='${stor.store_image}' />`
                                return false
                            }
                        })
                        if (img != '') {
                            return false
                        }
                    }
                })

                if (img == '') {
                    img = `<div class='contribNoImage'></div>`
                }

                var html = `<li id='#idContributorAction_${contributorId}'>
                              <div class='infos'>
                                 ${img}
                                  <span>${contributorName}</span>
                              </div>
                              <div class='links'></div>
                            </li>`

                selector.parent().find('.' + selectorName + ' ul').append(html)
                var existApm = false
                var existSpo = false
                var existYtm = false
                if (dataContrib.store_ids && $.isArray(dataContrib.store_ids)) {
                    $.each(dataContrib.store_ids, function(k, stor) {
                        if (settings.showEmpty || (stor.id && typeof stor.id != 'undefined')) {
                            if (typeof settings.dispoStoreIds[stor.id] != 'undefined' && ($.inArray(parseInt(stor.id), settings.dispoIds)) > -1) {
                                if (stor.id == 0) {
                                    existApm = true
                                    var link = `<a class='link_artist apple' target='_blank' href='${getUrlStore(stor.id)}${stor.store_id}'><i class='fab fa-itunes'></i></a>`
                                } else if (stor.id == 204) {
                                    existSpo = true
                                    var link = `<a class='link_artist spotify' target='_blank' href='${getUrlStore(stor.id)}${stor.store_id}'><i class='fab fa-spotify'></a>`
                                } else if (stor.id == 334) {
                                    existYtm = true
                                    var link = `<a class='link_artist ytm' target='_blank' href='${getUrlStore(stor.id)}${stor.store_id}'><i class='fab fa-youtube'></i></a>`
                                }
                                selector.parent().find('.' + selectorName + ' ul li .links').append(link)
                            }
                        }
                    })
                }
                if (!existSpo) {
                    selector.parent().find('.' + selectorName + ' ul li .links').append('<div class="addSAID spo editBySotreId" data-mode="edit" data-contributorId="' + contributorId + '" data-idContrib="' + contributorId + '" data-title="' + selectorName + '" id="searchSpotify">+<i class="fab fa-spotify"></i></div>')
                }
                if (!existApm) {
                    selector.parent().find('.' + selectorName + ' ul li .links').append('<div class="addSAID apm editBySotreId" data-mode="edit" data-idContrib="' + contributorId + '" data-contributorId="' + contributorId + '" data-title="' + selectorName + '" id="searchiTunes">+<i class="fab fa-itunes-note"></i></div>')
                }
                if (!existYtm) {
                    selector.parent().find('.' + selectorName + ' ul li .links').append('<div class="addSAID ytm editBySotreId" data-mode="edit" data-idContrib="' + contributorId + '" data-contributorId="' + contributorId + '" data-title="' + selectorName + '" id="searchYoutube">+<i class="fab fa-youtube"></i></div>')
                }

                selector.parent().find('.' + selectorName + ' ul li .links').append('<div class="deleteContributor" style="display: none"><i class="fas fa-times"></i></div>')
                if (true !== selector.data('hideDeleteBtn')) {
                    selector.parent().find('.deleteContributor').css('display', 'block')
                }

                $('.' + selectorName).after(showButtonSearch(selectorName))
                init(selectorName)
                selector.hide()
            } else {
                addNotInDbMsg(selectorName)
                selector.show()
            }
        }

        /**
         * search Artist Picker By Id
         * @param string id
         * @return void
         */
        function searchArtistPickerById(id, type = settings.albumType) {
            var contrib = getContributorById(id, type)
            if (contrib['data']) {
                showContributor(contrib['data'])
            }
        }

        /**
         * add Contributor Info To List
         * @param array data
         * @return void
         */
        function addContributorInfoToList(data) {
            var contributorId = data.idContributor
            var contributorName = data.contributorName
            var pendingId = data.pending_id

            var img = ''
            var apple = ''
            var spo = ''
            var ytm = ''
            $.each(data, function(m, store) {
                if (img == '' && store.store_id && typeof store.store_id != 'undefined' && store.store_image) {
                    img = `<img src='${store.store_image}' />`
                }
                if (store.id == '0') {
                    apple = store.store_id
                }
                if (store.id == '204') {
                    spo = store.store_id
                }
                if (store.id == '334') {
                    ytm = store.store_id
                }
            })

            if (img == '') {
                img = `<div class='contribNoImage'></div>`
            }
            var appleLink = apple ? `<a class='link_tooltip' href='https://music.apple.com/us/artist/${apple}' target='_blank'>
                                <i class='fab fa-itunes'></i>
                                <span class='tooltiptext'>${apple}</span>
                            </a>` : ''
            var spoLink = spo ? `<a class='link_tooltip' href='https://open.spotify.com/artist/${spo}' target='_blank'>
                                <i class='fab fa-spotify'></i>
                                <span class='tooltiptext'>${spo}</span>
                            </a>` : ''
            var ytmLink = ytm ? `<a class='link_tooltip ytm' href='https://youtube.com/channel/${ytm}' target='_blank'>
                                <i class='fab fa-youtube'></i>
                                <span class='tooltiptext'>${ytm}</span>
                            </a>` : ''

            var html = `<li>
                          <a  class='idContributorAction' 
                              data-selector='${selectorName}' 
                              data-title='${contributorName}' 
                              data-value='${contributorId}' 
                              data-pendingId='${pendingId}' 
                              id='idContributorAction_${contributorId}'>
                                ${img} 
                                <span>${contributorName}</span>
                          </a>
                          <div class='storeIds'>
                            ${appleLink}
                            ${spoLink}  
                            ${ytmLink}                            
                          </div>
                         </li>`

            selector.parent().find('.' + selectorName + ' ul').append(html)
        }


        /**
         * get all Contributor Info
         * @param array data
         * @return array tab
         */
        function getAllContributorInfo(datas) {
            var tab = []
            $.each(datas, function(k, data) {
                if (data.id) {
                    data.store_ids.idContributor = data.id
                    data.store_ids.contributorName = data.name
                    data.store_ids.pending_id = data.pending_id
                    data.store_ids.relevancy = data.relevancy
                    tab[k] = data.store_ids
                }
            })
            return tab
        }

        /**
         * add no in db msg
         * @param string selectorName
         * @return void
         */
        function addNotInDbMsg(selectorName) {
            if ($.trim(selector.val()) != '') {
                $('.' + selectorName).remove()
                $('#selectedList' + selectorName).remove()
                $('.selected-main_' + selectorName).remove()
                $('.selected-main_insert_' + selectorName).remove()
                $('.' + selectorName + '_msgNoInBd').remove()
                $('#content_' + selectorName + '.selected-storeId').remove()
                selector.after('<div class="content-main" id="content_' + selectorName + '" ><div class="' + selectorName + '_msgNoInBd msgNoInBd">' +
                    '<div class="findOn"><i class="fas fa-search"></i>' + EEAP.EEAP_FINDON + '</div>' +
                    showButtonSearch(selectorName) +
                    '</div></div>')
                $('.' + selectorName + '_msgNoInBd').find('.searchNotInDb').css('display', 'flex')
                init(selectorName)
            }
        }

        /**
         * show button search
         * @param string selectorName
         * @return void
         */
        function showButtonSearch(selectorName) {
            return '<div class="' + selectorName + '_msgNoInBd msgNoInBd"><ul class="searchOtherBlv">' +
                '<li><div class="searchNotInDb button" data-mode="add" data-title="' + selectorName + '" id="searchiTunes"><i class="fab fa-itunes"></i> Apple Music</div></li>' +
                '<li><div class="searchNotInDb button" data-mode="add" data-title="' + selectorName + '" id="searchSpotify"><i class="fab fa-spotify"></i> Spotify</div></li>' +
                '   <li><div class="searchContent" id="searchiTunesContent' + selectorName + '">' + getHmtl(0, selectorName) + '</div></li>' +
                '   <li><div class="searchContent" id="searchSpotifyContent' + selectorName + '">' + getHmtl(204, selectorName) + '</div></li>' +
                '   <li><div class="searchContent youtube" id="searchYoutubeContent' + selectorName + '">' + getHmtl(334, selectorName) + '</div></li>' +
                ' </ul></div>'

        }

        /**
         * get Name By IdStore
         * @param int idStore
         * @return string
         */
        function getNameByIdStore(idStore) {
            return $.trim(getNameById(idStore).replace('ID', ''))
        }

        /**
         * insert Ligne Error After Selector
         * @param string seletorName
         * @return void
         */
        function insertLigneErrorAfterSelector(seletorName, msg) {
            var itemSelcted = selector
            showMsgNotify(itemSelcted, selectorName, 'error', formatMessageError(msg))
        }

        /**
         * add List Storie Content
         * @param json dataSearchs
         * @param int idStore
         * @param string seletorName
         * @return void
         */
        function addListStorieContent(dataSearchs, idStore, seletorName, mode) {
            $('#searchOtherBlv2_' + idStore + seletorName).append('<li> ' + dataSearchs.length + ' ' + EEAP.EEAP_RESULTS + '</li>')
            $.each(dataSearchs, function(w, dataSearch) {
                var nameId = getNameById(idStore)
                var img = '<div class="noImageSquare"></div>'
                if (dataSearch.store_image) {
                    img = '<img class="imgStore" src="' + dataSearch.store_image + '">'
                }

                if (idStore !== '204') {
                    img = ''
                }

                var html = `<li class='artistProposal' onclick='checkboxByRow(this)'>
                                <input 
                                    type='radio' 
                                    data-seletorName='${seletorName}' 
                                    data-type='select' 
                                    data-idStore='${idStore}' 
                                    data-name='${dataSearch.name}' 
                                    data-img='${img}' 
                                    data-idContributor='${dataSearch.store_id}' 
                                    name='storeselected'>
                                ${img}
                                <div class='artistNameAndId'>
                                    <span class='name'>${dataSearch.name}</span>
                                    <a target='_blank' href='${getUrlStore(idStore)}${dataSearch.store_id}' class='id'>${dataSearch.store_id}</a>
                                </div>
                            </li>`

                $('#searchOtherBlv2_' + idStore + seletorName).append(html)
            })
            addLineInListeStoeir(idStore, seletorName, mode)
        }

        /**
         * add Line In ListeStoeir
         * @param int idStore
         * @param string seletorName
         * @return void
         */
        function addLineInListeStoeir(idStore, seletorName, mode) {
            var name = getNameByIdStore(idStore)
            var nameContib = selector.val()
            $('#default-value_' + idStore + seletorName + '').append('<div class="desc">' +
                '<input type="radio" name="storeselected"  data-seletorName="' + seletorName + '" data-type="input" data-idStore="' + idStore + '" >' + EEAP.EEAP_MANUALLY_ADD + '</div>' +
                '<div class="input"><input onchange="autoCheckCheckbox(this);" onkeyup="this.onchange();" onpaste="this.onchange();" data-store="' + idStore + '" name="pageName_' + idStore + '" type="text"><span class="errorpagename"></span></div>')
            if ('add' == mode) {
                $('#default-value_' + idStore + seletorName + '').append('<div class="notInStore">' +
                    '<input type="radio" name="storeselected"  data-seletorName="' + seletorName + '" data-type="nothing"  data-idStore="' + idStore + '">' + EEAP.EEAP_NOT_FOUND.replace('%contrib_name%', nameContib).replace('%store_name%', name) + '</div>'
                )
            }

            $('#default-value_' + idStore + seletorName + ' ul').append('<li>' +
                '<input type="hidden" value="edit" data-idContrib="" id="mode_' + seletorName + '"></li>'
            )
        }

        /**
         * get StoreId By Name
         * @param string name
         * @return string
         */
        function getStoreIdByName(name) {
            var toReturn = false
            $.each(settings.dispoStoreIds, function(q, str) {
                if (parseInt(name.search(str)) > 0) {
                    toReturn = q
                }
            })
            return toReturn
        }

        /**
         * get name By id
         * @param int id
         * @return string
         */
        function getNameById(id) {
            return settings.dispoStoreIds[id] + ' ID '
        }

        /**
         * get data Contributors by name
         * @return json
         */
        function getData() {
            var mydata = {
                data: '',
                error: '',
                available: true
            }
            if ($.trim(selector.val()) != '') {
                $.ajax({
                    url: settings.urlAllContributors,
                    async: false,
                    type: 'POST',
                    dataType: 'json',
                    data: { name: encodeURIComponent(selector.val().trim()), idProducer: settings.idProducer },
                    beforeSend: function() {
                        $('body').append(getDivLoading())
                    },
                    complete: function() {
                        $('#loading').remove()
                    },
                    statusCode: {
                        200: function(json) {
                            mydata['data'] = json
                        },
                        400: function(json) {
                            mydata['error'] = json.responseJSON.message
                        },
                        404: function(json) {
                            mydata['error'] = json.responseJSON.message
                        },
                        409: function(json) {
                            mydata['error'] = json.responseJSON.message
                        },
                        500: function() {
                            mydata['available'] = false
                        }
                    }
                })
            }

            return mydata
        }

        /**
         * get data Contributors by name
         * @return json
         */
        function getExactContrib() {
            var mydata = {
                data: '',
                error: '',
                available: true
            }
            if ($.trim(selector.val()) != '') {
                $.ajax({
                    url: settings.urlAllExactContributors,
                    async: false,
                    type: 'POST',
                    dataType: 'json',
                    data: { name: encodeURIComponent(selector.val().trim()), idProducer: settings.idProducer },
                    beforeSend: function() {
                        $('body').append(getDivLoading())
                    },
                    complete: function() {
                        $('#loading').remove()
                    },
                    statusCode: {
                        200: function(json) {
                            mydata['data'] = json
                        },
                        400: function(json) {
                            mydata['error'] = json.responseJSON.message
                        },
                        404: function(json) {
                            mydata['error'] = json.responseJSON.message
                        },
                        409: function(json) {
                            mydata['error'] = json.responseJSON.message
                        },
                        500: function() {
                            mydata['available'] = false
                        }
                    }
                })
            }

            return mydata
        }

        /**
         * get data Contributors by id
         * @return json
         */
        function getContributorById(id, albumType) {
            var mydata = {
                data: '',
                error: '',
                available: true
            }
            var url = settings.urlContributor + id + '/' + albumType

            $.ajax({
                url: url,
                async: false,
                dataType: 'json',
                beforeSend: function() {
                    $('body').append(getDivLoading())
                },
                complete: function() {
                    $('#loading').remove()
                },
                statusCode: {
                    200: function(json) {
                        mydata['data'] = json
                    },
                    400: function(json) {
                        mydata['error'] = json.responseJSON.message
                    },
                    404: function(json) {
                        mydata['error'] = json.responseJSON.message
                    },
                    409: function(json) {
                        mydata['error'] = json.responseJSON.message
                    },
                    500: function() {
                        mydata['available'] = false
                    }
                }
            })
            return mydata
        }


        /**
         * get data Contributors by id
         * @return json
         */
        function createPendingIdById(id) {
            var mydata = {
                data: '',
                error: ''
            }
            albumType = 'final'
            var url = settings.urlContributor + id + '/' + albumType

            $.ajax({
                url: url,
                async: false,
                dataType: 'json',
                beforeSend: function() {
                    $('body').append(getDivLoading())
                },
                complete: function() {
                    $('#loading').remove()
                },
                statusCode: {
                    200: function(json) {
                        mydata['data'] = json
                    },
                    400: function(json) {
                        mydata['error'] = json.responseJSON.message
                    },
                    404: function(json) {
                        mydata['error'] = json.responseJSON.message
                    },
                    409: function(json) {
                        mydata['error'] = json.responseJSON.message
                    }
                }
            })
            return mydata
        }

        /**
         * get data Contributors in api stores
         * @param string name
         * @param int storeId
         * @return json
         */
        function searchInApiStores(name, sotoreId) {
            var mydata = {
                data: '',
                error: ''
            }
            $.ajax({
                url: settings.urlApiStores + sotoreId,
                async: false,
                type: 'POST',
                dataType: 'json',
                data: { name: encodeURIComponent(name.trim()) },
                beforeSend: function() {
                    $('body').append(getDivLoading())
                },
                complete: function() {
                    $('#loading').remove()
                },
                statusCode: {
                    200: function(json) {
                        mydata['data'] = json
                    },
                    400: function(json) {
                        mydata['error'] = json.responseJSON.message
                    },
                    404: function(json) {
                        mydata['error'] = json.responseJSON.message
                    },
                    409: function(json) {
                        mydata['error'] = json.responseJSON.message
                    }
                }
            })
            return mydata
        }

        /**
         * create new Contributor
         * @param string name
         * @param int idProducer
         * @param array storeId
         * @return json
         */
        function createNewContib(name, idProducer, storeIds) {
            var mydataRetour = {
                data: '',
                error: ''
            }
            $.ajax({
                url: settings.urlContribInsert,
                type: 'POST',
                dataType: 'json',
                async: false,
                data: { name: name, idProducer: idProducer, storeIds: storeIds },
                beforeSend: function() {
                    $('body').append(getDivLoading())
                },
                complete: function() {
                    $('#loading').remove()
                },
                statusCode: {
                    200: function(json) {
                        mydataRetour['data'] = json
                    },
                    400: function(json) {
                        mydataRetour['error'] = json.responseJSON.message
                    },
                    404: function(json) {
                        mydataRetour['error'] = json.responseJSON.message
                    },
                    409: function(json) {
                        mydataRetour['error'] = json.responseJSON.message
                    }
                }
            })
            return mydataRetour

        }

        /**
         * update Contributor
         * @param int idcontrib
         * @param array storeId
         * @return json
         */
        function updateContib(idcontrib, storeIds) {
            var mydataRetour = {
                data: '',
                error: ''
            }

            var albumType = 'final'

            $.ajax({
                url: settings.urlContribUpdate,
                type: 'POST',
                dataType: 'json',
                async: false,
                data: { idcontrib: idcontrib, storeIds: storeIds, albumType: albumType },
                beforeSend: function() {
                    $('body').append(getDivLoading())
                },
                complete: function() {
                    $('#loading').remove()
                },
                statusCode: {
                    200: function(json) {
                        mydataRetour['data'] = json
                    },
                    400: function(json) {
                        mydataRetour['error'] = json.responseJSON.message
                    },
                    404: function(json) {
                        mydataRetour['error'] = json.responseJSON.message
                    },
                    409: function(json) {
                        mydataRetour['error'] = json.responseJSON.message
                        var strr = json.responseJSON.message
                    }
                }
            })
            return mydataRetour

        }

        /**
         * get html popin search in api
         * @param array storeId
         * @param string selectorName
         * @return string
         */
        function getHmtl(idStore, selectorName) {
            return '<div id="dialog-message' + idStore + '" class="ui-dialog-content ui-widget-content">' +
                '  <div class="ui-state-default">' +
                '  </div>' +
                '    <div id="contentMsg_' + idStore + '">' +
                '       <div>' +
                '       <ul class="searchOtherBlv2" id="searchOtherBlv2_' + idStore + selectorName + '">' +
                '       </ul>' +
                '   </div>' +
                '    </div>' +
                '<div class="default-value" id="default-value_' + idStore + selectorName + '">' +
                '</div>' +
                '</div>' +
                '<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix">' +
                '<div class="ui-dialog-buttonset div-action">' +
                '   <span id="button-cancel">' + EEAP.EEAP_CANCEL + '</span>' +
                '   <span id="button-ok_' + selectorName + '" class="button-ok" name="ok_' + idStore + '">' + EEAP.EEAP_OK + '</span></div>'
            '</div>'
        }

        /**
         * get html div loading
         * @return string
         */
        function getDivLoading() {
            return '<div id="loading">' +
                '    <div id="loading-image" alt="Loading..." ></div>' +
                '</div>'
        }

        /**
         * show Msg Notify
         * @param object itemSelcted
         * @param string selectorName
         * @param string notifyType
         * @param string mode
         * @return void
         */
        function showMsgNotify(itemSelcted, selectorName, notifyType, msg, mode) {
            switch (notifyType) {
                case 'succes':
                    $('.alert-success_' + selectorName).remove()
                    $('.alert-danger_' + selectorName).remove()
                    itemSelcted.after(getMsgSucces(selectorName, mode))
                    showMsg('alert-success_' + selectorName, notifyType)
                    break
                case 'error':
                    $('.alert-danger_' + selectorName).remove()
                    itemSelcted.after(getMsgError(selectorName, msg))
                    showMsg('alert-danger_' + selectorName, notifyType)
                    break
            }

        }

        /**
         * get Msg Succes
         * @param string selectorName
         * @param string mode
         * @return string
         */
        function getMsgSucces(selectorName, mode) {
            $('.' + selectorName).remove()
            $('#content_' + selectorName + '.selected-storeId').remove()
            var action = 'created'
            if ('edit' == mode) {
                action = 'updated'
            }
            return '<div class="alert alert-success alert-success_' + selectorName + '" role="alert">' +
                EEAP.EEAP_ARTIST_ADDED.replace('%action%', action) +
                '</div>'
        }

        /**
         * get Msg error
         * @param string selectorName
         * @param string msg
         * @return string
         */
        function getMsgError(selectorName, msg) {
            return '<div class=" artistpicker_alert alert alert-danger alert-danger_' + selectorName + '" role="alert">' + msg + '</div>'
        }

        /**
         * show Msg
         * @param string id
         * @return void
         */
        function showMsg(id, notifyType) {
            if ('error' == notifyType) {
                $('.' + id).show('slow')
            } else {
                $('.' + id).fadeTo(2000, 500).slideUp(500, function() {
                    $('.' + id).slideUp(500)
                })
            }
        }

        /**
         * formatMessageError
         * @param string msg
         * @return sting
         */
        function formatMessageError(msg) {
            return msg.length > 76 ? msg.substring(0, 76) + ' ...' : msg
        }

        if ($(this).length > 1) {
            $(this).each(function() {
                $(this).artistPickerSimple()
                destroy(selectorName)
            })
        }

        return this
    }

})(jQuery)